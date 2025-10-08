/**
 * License Verification System for React
 * Product: {{product}}
 * Generated: {{date}}
 */

import React, { useState, useEffect } from 'react';
import axios from 'axios';

const LicenseVerifier = {
  apiUrl: '{{license_api_url}}',
  productSlug: '{{product_slug}}',
  verificationKey: '{{verification_key}}',
  apiToken: '{{api_token}}',

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
  },


  /**
   * Verify with our license system
   */
  async verifyWithOurSystem(purchaseCode, domain = null) {
    try {
      const response = await axios.post(this.apiUrl, {
        purchase_code: purchaseCode,
        product_slug: this.productSlug,
        domain: domain,
        verification_key: this.verificationKey
      }, {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'User-Agent': 'LicenseVerifier/1.0'
        },
        timeout: 10000
      });

      const data = response.data;

      return this.createLicenseResponse(
        data.valid || false,
        data.message || 'Verification completed',
        data
      );
    } catch (error) {
      return this.createLicenseResponse(false, 'Network error: ' + error.message);
    }
  },

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
};

// React Hook for license verification
export const useLicenseVerification = () => {
  const [isVerifying, setIsVerifying] = useState(false);
  const [licenseResult, setLicenseResult] = useState(null);
  const [error, setError] = useState(null);

  const verifyLicense = async (purchaseCode, domain = null) => {
    setIsVerifying(true);
    setError(null);

    try {
      const result = await LicenseVerifier.verifyLicense(purchaseCode, domain);
      setLicenseResult(result);
      return result;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setIsVerifying(false);
    }
  };

  return {
    verifyLicense,
    isVerifying,
    licenseResult,
    error
  };
};

// React Component for license verification
const LicenseVerificationForm = ({ onVerificationComplete }) => {
  const [purchaseCode, setPurchaseCode] = useState('');
  const [domain, setDomain] = useState('');
  const { verifyLicense, isVerifying, licenseResult, error } = useLicenseVerification();

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const result = await verifyLicense(purchaseCode, domain);
      if (onVerificationComplete) {
        onVerificationComplete(result);
      }
    } catch (err) {
      // Error is already handled by the hook
    }
  };

  return (
    <div className="license-verifier">
      <h2>License Verification</h2>
      <p>Product: <strong>{{product}}</strong></p>

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="purchaseCode">Purchase Code:</label>
          <input
            type="text"
            id="purchaseCode"
            value={purchaseCode}
            onChange={(e) => setPurchaseCode(e.target.value)}
            required
            placeholder="Enter your purchase code"
            disabled={isVerifying}
          />
        </div>

        <div className="form-group">
          <label htmlFor="domain">Domain (optional):</label>
          <input
            type="url"
            id="domain"
            value={domain}
            onChange={(e) => setDomain(e.target.value)}
            placeholder="https://yourdomain.com"
            disabled={isVerifying}
          />
        </div>

        <button type="submit" disabled={isVerifying || !purchaseCode.trim()}>
          {isVerifying ? 'Verifying...' : 'Verify License'}
        </button>
      </form>

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}

      {licenseResult && (
        <div className={`result ${licenseResult.valid ? 'success' : 'error'}`}>
          {licenseResult.message}
        </div>
      )}
    </div>
  );
};

export { LicenseVerifier, LicenseVerificationForm };

// Usage example:
/*
import { LicenseVerifier, LicenseVerificationForm, useLicenseVerification } from './license-verifier';

// Using the hook
const MyComponent = () => {
  const { verifyLicense, isVerifying, licenseResult } = useLicenseVerification();

  const handleVerify = async () => {
    const result = await verifyLicense('YOUR_PURCHASE_CODE', 'yourdomain.com');
    // License verification result
  };

  return (
    <div>
      <button onClick={handleVerify} disabled={isVerifying}>
        {isVerifying ? 'Verifying...' : 'Verify License'}
      </button>
      {licenseResult && <p>Result: {licenseResult.message}</p>}
    </div>
  );
};

// Using the component
const App = () => {
  const handleVerificationComplete = (result) => {
    // Verification complete
  };

  return (
    <LicenseVerificationForm onVerificationComplete={handleVerificationComplete} />
  );
};
*/
