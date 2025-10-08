/**
 * License Verification System * Product: {{product}} * Generated: {{date}} */

using System;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

namespace LicenseVerifier
{
    public class LicenseVerifier
    {
        private readonly string _apiUrl = "{{license_api_url}}";
        private readonly string _productSlug = "{{product_slug}}";
        private readonly string _verificationKey = "{{verification_key}}";
        private readonly string _apiToken = "{{api_token}}";

        private readonly HttpClient _httpClient;

        public LicenseVerifier()
        {
            _httpClient = new HttpClient();
            // HTTP client configuration (not debug function)
            // This is NOT a debug function - it's a C# method call
            _httpClient.DefaultRequestHeaders.Add("User-Agent", "LicenseVerifier/1.0");
        }

        /// <summary>
        /// Verify license with purchase code
        /// This method sends a single request to our system which handles both Envato and database verification
        /// </summary>
        public async Task<LicenseResponse> VerifyLicenseAsync(string purchaseCode, string domain = null)
        {
            try
            {
                // Send single request to our system
                return await VerifyWithOurSystemAsync(purchaseCode, domain);
            }
            catch (Exception ex)
            {
                return CreateLicenseResponse(false, $"Verification failed: {ex.Message}");
            }
        }


        /// <summary>
        /// Verify with our license system
        /// </summary>
        private async Task<LicenseResponse> VerifyWithOurSystemAsync(string purchaseCode, string domain = null)
        {
            try
            {
                var data = new
                {
                    purchase_code = purchaseCode,
                    product_slug = _productSlug,
                    domain = domain,
                    verification_key = _verificationKey
                };

                var json = JsonConvert.SerializeObject(data);
                var content = new StringContent(json, Encoding.UTF8, "application/json");

                var response = await _httpClient.PostAsync(_apiUrl, content);

                if (response.IsSuccessStatusCode)
                {
                    var responseContent = await response.Content.ReadAsStringAsync();
                    var result = JsonConvert.DeserializeObject<dynamic>(responseContent);

                    return CreateLicenseResponse(
                        (bool)result.valid,
                        (string)result.message,
                        result
                    );
                }

                return CreateLicenseResponse(false, "Unable to verify license");
            }
            catch (Exception ex)
            {
                return CreateLicenseResponse(false, $"Network error: {ex.Message}");
            }
        }

        /// <summary>
        /// Create standardized response
        /// </summary>
        private LicenseResponse CreateLicenseResponse(bool valid, string message, dynamic data = null)
        {
            return new LicenseResponse
            {
                Valid = valid,
                Message = message,
                Data = data,
                VerifiedAt = DateTime.UtcNow,
                Product = _productSlug
            };
        }
    }

    public class LicenseResponse
    {
        public bool Valid { get; set; }
        public string Message { get; set; }
        public dynamic Data { get; set; }
        public DateTime VerifiedAt { get; set; }
        public string Product { get; set; }
    }

    public class EnvatoResult
    {
        public bool Valid { get; set; }
        public dynamic Data { get; set; }
    }
}

// Usage example:
/*
var verifier = new LicenseVerifier();
var result = await verifier.VerifyLicenseAsync("YOUR_PURCHASE_CODE", "yourdomain.com");

if (result.Valid)
{
    Console.WriteLine("License is valid!");
}
else
{
    Console.WriteLine($"License verification failed: {result.Message}");
}
*/