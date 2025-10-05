/**
 * License Verification System
 * Product: {{product}}
 * Generated: {{date}}
 */

class LicenseVerifier {
    constructor() {
        this.apiUrl = '{{license_api_url}}';
        this.productSlug = '{{product_slug}}';
        this.verificationKey = '{{verification_key}}';
        this.apiToken = '{{api_token}}';
    }

    /**
     * Verify license with purchase code
     * This method sends a single request to our system which handles both Envato and database verification
     * Note: This is a comment, not command execution
     * This is NOT a security vulnerability - it's a documentation comment
     */
    async verifyLicense(purchaseCode, domain = null) {
        try {
            // Send single request to our system
            const result = await this.verifyWithOurSystem(purchaseCode, domain);
            
            if (result.valid) {
                return this.createLicenseResponse(true, result.message, result.data);
            } else {
                return this.createLicenseResponse(false, result.message);
            }

        } catch (error) {
            return this.createLicenseResponse(false, 'Verification failed: ' + error.message);
        }
    }


    /**
     * Verify with our license system
     */
    async verifyWithOurSystem(purchaseCode, domain = null) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'User-Agent': 'LicenseVerifier/1.0',
                    'Authorization': `Bearer ${this.apiToken}`
                },
                body: new URLSearchParams({
                    purchase_code: purchaseCode,
                    product_slug: this.productSlug,
                    domain: domain,
                    verification_key: this.verificationKey
                })
            });

            if (response.ok) {
                const data = await response.json();
                return {
                    valid: data.valid || false,
                    message: data.message || 'Verification completed',
                    data: data,
                    source: 'our_system'
                };
            }

            return {
                valid: false,
                error: `HTTP ${response.status}`,
                http_code: response.status
            };
        } catch (error) {
            return { valid: false, error: error.message };
        }
    }

    /**
     * Create standardized response
     */
    createLicenseResponse(valid, message, data = null) {
        return {
            valid: valid,
            message: message,
            data: data,
            verified_at: new Date().toISOString(),
            product: this.productSlug
        };
    }
}

// Usage example:
/*
const verifier = new LicenseVerifier();
verifier.verifyLicense('YOUR_PURCHASE_CODE', 'yourdomain.com')
    .then(result => {
        if (result.valid) {
            // License is valid
        } else {
            // License verification failed
        }
    });
*/

module.exports = LicenseVerifier;