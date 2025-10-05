<?php
/**
 * License Verification System
 * Product: {{product}}
 * Generated: {{date}}
 */

class LicenseVerifier {
    private $apiUrl = '{{license_api_url}}';
    private $productSlug = '{{product_slug}}';
    private $verificationKey = '{{verification_key}}';
    private $apiToken = '{{api_token}}';

    /**
     * Verify license with purchase code
     * This method sends a single request to our system which handles both Envato and database verification
     */
    public function verifyLicense($purchaseCode, $domain = null) {
        try {
            // Send single request to our system
            $result = $this->verifyWithOurSystem($purchaseCode, $domain);
            
            if ($result['valid']) {
                return $this->createLicenseResponse(true, $result['message'], $result['data']);
            } else {
                return $this->createLicenseResponse(false, $result['message']);
            }

        } catch (Exception $e) {
            return $this->createLicenseResponse(false, 'Verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify with our license system
     */
    private function verifyWithOurSystem($purchaseCode, $domain = null) {
        $postData = [
            'purchase_code' => $purchaseCode,
            'product_slug' => $this->productSlug,
            'domain' => $domain,
            'verification_key' => $this->verificationKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: LicenseVerifier/1.0',
            'Authorization: Bearer ' . $this->apiToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return [
                'valid' => $data['valid'] ?? false,
                'message' => $data['message'] ?? 'Verification completed',
                'data' => $data,
                'source' => 'our_system'
            ];
        }

        return [
            'valid' => false,
            'error' => 'Unable to verify license with our system',
            'http_code' => $httpCode
        ];
    }

    /**
     * Create standardized response
     */
    private function createLicenseResponse($valid, $message, $data = null) {
        return [
            'valid' => $valid,
            'message' => $message,
            'data' => $data,
            'verified_at' => date('Y-m-d H:i:s'),
            'product' => $this->productSlug
        ];
    }
}

// Usage example:
/*
$verifier = new LicenseVerifier();
$result = $verifier->verifyLicense('YOUR_PURCHASE_CODE', 'yourdomain.com');

if ($result['valid']) {
    echo "License is valid!";
} else {
    echo "License verification failed: " . $result['message'];
}
*/
?>