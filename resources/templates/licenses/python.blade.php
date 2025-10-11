"""
License Verification System
Product: {{product}}
Generated: {{date}}
"""

import requests
import json
from datetime import datetime

class LicenseVerifier:
    def __init__(self):
        self.api_url = '{{license_api_url}}'
        self.product_slug = '{{product_slug}}'
        self.verification_key = '{{verification_key}}'
        self.api_token = '{{api_token}}'

    def verify_license(self, purchase_code, domain=None):
        """
        Verify license with purchase code
        This method sends a single request to our system which handles both Envato and database verification
        """
        try:
            # Send single request to our system
            result = self._verify_with_our_system(purchase_code, domain)
            
            if result['valid']:
                return self._create_license_response(True, result['message'], result['data'])
            else:
                return self._create_license_response(False, result['message'])

        except Exception as e:
            return self._create_license_response(False, f'Verification failed: {str(e)}')


    def _verify_with_our_system(self, purchase_code, domain=None):
        """
        Verify with our license system
        """
        try:
            data = {
                'purchase_code': purchase_code,
                'product_slug': self.product_slug,
                'domain': domain,
                'verification_key': self.verification_key
            }

            response = requests.post(
                self.api_url,
                data=data,
                headers={
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'User-Agent': 'LicenseVerifier/1.0',
                    'Authorization': f'Bearer {self.api_token}'
                },
                timeout=10
            )

            if response.status_code == 200:
                result = response.json()
                return self._create_license_response(
                    result.get('valid', False),
                    result.get('message', 'Verification completed'),
                    result
                )

            return self._create_license_response(False, 'Unable to verify license')
        except Exception as e:
            return self._create_license_response(False, f'Network error: {str(e)}')

    def _create_license_response(self, valid, message, data=None):
        """
        Create standardized response
        """
        return {
            'valid': valid,
            'message': message,
            'data': data,
            'verified_at': datetime.now().isoformat(),
            'product': self.product_slug
        }

# Usage example:
"""
verifier = LicenseVerifier()
result = verifier.verify_license('YOUR_PURCHASE_CODE', 'yourdomain.com')

if result['valid']:
    print('License is valid!')
else:
    print(f'License verification failed: {result["message"]}')
"""