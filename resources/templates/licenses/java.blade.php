/**
 * License Verification System
 * Product: {{product}}
 * Generated: {{date}}
 */

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

public class LicenseVerifier {
    private static final String API_URL = "{{license_api_url}}";
    private static final String PRODUCT_SLUG = "{{product_slug}}";
    private static final String VERIFICATION_KEY = "{{verification_key}}";
    private static final String API_TOKEN = "{{api_token}}";

    private final ObjectMapper objectMapper;

    public LicenseVerifier() {
        this.objectMapper = new ObjectMapper();
    }

    /**
     * Verify license with purchase code
     * This method sends a single request to our system which handles both Envato and database verification
     */
    public LicenseResponse verifyLicense(String purchaseCode, String domain) {
        try {
            // Send single request to our system
            return verifyWithOurSystem(purchaseCode, domain);

        } catch (Exception e) {
            return createLicenseResponse(false, "Verification failed: " + e.getMessage());
        }
    }


    /**
     * Verify with our license system
     */
    private LicenseResponse verifyWithOurSystem(String purchaseCode, String domain) {
        try {
            URL url = new URL(API_URL);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("POST");
            conn.setRequestProperty("Content-Type", "application/json");
            conn.setRequestProperty("User-Agent", "LicenseVerifier/1.0");
            conn.setDoOutput(true);

            // Create JSON payload
            String jsonInputString = String.format(
                "{\"purchase_code\":\"%s\",\"product_slug\":\"%s\",\"domain\":\"%s\",\"verification_key\":\"%s\"}",
                purchaseCode, PRODUCT_SLUG, domain != null ? domain : "", VERIFICATION_KEY
            );

            try (OutputStream os = conn.getOutputStream()) {
                byte[] input = jsonInputString.getBytes("utf-8");
                os.write(input, 0, input.length);
            }

            int responseCode = conn.getResponseCode();

            if (responseCode == 200) {
                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String inputLine;

                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();

                JsonNode result = objectMapper.readTree(response.toString());

                return createLicenseResponse(
                    result.get("valid").asBoolean(),
                    result.get("message").asText(),
                    result
                );
            }

            return createLicenseResponse(false, "Unable to verify license");
        } catch (Exception e) {
            return createLicenseResponse(false, "Network error: " + e.getMessage());
        }
    }

    /**
     * Create standardized response
     */
    private LicenseResponse createLicenseResponse(boolean valid, String message, JsonNode data) {
        return new LicenseResponse(valid, message, data, LocalDateTime.now(), PRODUCT_SLUG);
    }

    // Inner classes for responses
    public static class LicenseResponse {
        private final boolean valid;
        private final String message;
        private final JsonNode data;
        private final LocalDateTime verifiedAt;
        private final String product;

        public LicenseResponse(boolean valid, String message, JsonNode data, LocalDateTime verifiedAt, String product) {
            this.valid = valid;
            this.message = message;
            this.data = data;
            this.verifiedAt = verifiedAt;
            this.product = product;
        }

        // Getters
        public boolean isValid() { return valid; }
        public String getMessage() { return message; }
        public JsonNode getData() { return data; }
        public LocalDateTime getVerifiedAt() { return verifiedAt; }
        public String getProduct() { return product; }
    }

    public static class EnvatoResult {
        private final boolean valid;
        private final JsonNode data;

        public EnvatoResult(boolean valid, JsonNode data) {
            this.valid = valid;
            this.data = data;
        }

        public boolean isValid() { return valid; }
        public JsonNode getData() { return data; }
    }
}

// Usage example:
/*
LicenseVerifier verifier = new LicenseVerifier();
LicenseVerifier.LicenseResponse result = verifier.verifyLicense("YOUR_PURCHASE_CODE", "yourdomain.com");

if (result.isValid()) {
    System.out.println("License is valid!");
} else {
    System.out.println("License verification failed: " + result.getMessage());
}
*/