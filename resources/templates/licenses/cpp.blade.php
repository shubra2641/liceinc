/**
 * License Verification System for C++
 * Product: {{product}}
 * Generated: {{date}}
 */

#include <iostream>
#include <string>
#include <curl/curl.h>
#include <nlohmann/json.hpp>
#include <chrono>
#include <iomanip>
#include <sstream>

using json = nlohmann::json;

class LicenseVerifier {
private:
    std::string api_url = "{{license_api_url}}";
    std::string product_slug = "{{product_slug}}";
    std::string verification_key = "{{verification_key}}";
    std::string api_token = "{{api_token}}";

    // Callback function for curl
    static size_t WriteCallback(void* contents, size_t size, size_t nmemb, std::string* userp) {
        userp->append((char*)contents, size * nmemb);
        return size * nmemb;
    }

public:
    struct LicenseResponse {
        bool valid;
        std::string message;
        json data;
        std::string verified_at;
        std::string product;

        LicenseResponse(bool v, std::string m, json d = nullptr, std::string p = "")
            : valid(v), message(m), data(d), product(p) {
            auto now = std::chrono::system_clock::now();
            auto time_t = std::chrono::system_clock::to_time_t(now);
            std::stringstream ss;
            ss << std::put_time(std::gmtime(&time_t), "%Y-%m-%dT%H:%M:%SZ");
            verified_at = ss.str();
        }
    };

    /**
     * Verify license with purchase code
     * This method sends a single request to our system which handles both Envato and database verification
     */
    LicenseResponse verifyLicense(const std::string& purchase_code, const std::string& domain = "") {
        try {
            // Send single request to our system
            return verifyWithOurSystem(purchase_code, domain);

        } catch (const std::exception& e) {
            return createLicenseResponse(false, "Verification failed: " + std::string(e.what()));
        }
    }

private:

    /**
     * Verify with our license system
     */
    LicenseResponse verifyWithOurSystem(const std::string& purchase_code, const std::string& domain = "") {
        CURL* curl = curl_easy_init();
        std::string response_string;

        if (curl) {
            curl_easy_setopt(curl, CURLOPT_URL, api_url.c_str());
            curl_easy_setopt(curl, CURLOPT_POST, 1L);
            curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, WriteCallback);
            curl_easy_setopt(curl, CURLOPT_WRITEDATA, &response_string);

            // Create POST data
            std::string post_data = "purchase_code=" + curl_easy_escape(curl, purchase_code.c_str(), purchase_code.length()) +
                                  "&product_slug=" + curl_easy_escape(curl, product_slug.c_str(), product_slug.length()) +
                                  "&verification_key=" + curl_easy_escape(curl, verification_key.c_str(), verification_key.length());

            if (!domain.empty()) {
                post_data += "&domain=" + curl_easy_escape(curl, domain.c_str(), domain.length());
            }

            curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post_data.c_str());

            struct curl_slist* headers = NULL;
            headers = curl_slist_append(headers, "Content-Type: application/x-www-form-urlencoded");
            headers = curl_slist_append(headers, "User-Agent: LicenseVerifier/1.0");
            curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);

            CURLcode res = curl_easy_perform(curl);

            if (res == CURLE_OK) {
                long response_code;
                curl_easy_getinfo(curl, CURLINFO_RESPONSE_CODE, &response_code);

                if (response_code == 200) {
                    try {
                        json result = json::parse(response_string);

                        bool valid = result.value("valid", false);
                        std::string message = result.value("message", "Verification completed");

                        curl_easy_cleanup(curl);
                        curl_slist_free_all(headers);
                        return createLicenseResponse(valid, message, result);
                    } catch (const json::parse_error& e) {
                        curl_easy_cleanup(curl);
                        curl_slist_free_all(headers);
                        return createLicenseResponse(false, "Invalid response format");
                    }
                }
            }

            curl_easy_cleanup(curl);
            curl_slist_free_all(headers);
        }

        return createLicenseResponse(false, "Unable to verify license");
    }

    /**
     * Create standardized response
     */
    LicenseResponse createLicenseResponse(bool valid, const std::string& message, json data = nullptr) {
        return LicenseResponse(valid, message, data, product_slug);
    }
};

// Usage example:
/*
int main() {
    LicenseVerifier verifier;

    // Verify license
    auto result = verifier.verifyLicense("YOUR_PURCHASE_CODE", "yourdomain.com");

    if (result.valid) {
        std::cout << "License is valid!" << std::endl;
        std::cout << "Message: " << result.message << std::endl;
    } else {
        std::cout << "License verification failed: " << result.message << std::endl;
    }

    return 0;
}
*/

// CMakeLists.txt example:
/*
cmake_minimum_required(VERSION 3.10)
project(LicenseVerifier)

set(CMAKE_CXX_STANDARD 17)

# Find packages
find_package(CURL REQUIRED)
find_package(nlohmann_json REQUIRED)

# Add executable
add_executable(license_verifier main.cpp)

# Link libraries
target_link_libraries(license_verifier CURL::libcurl nlohmann_json::nlohmann_json)
*/