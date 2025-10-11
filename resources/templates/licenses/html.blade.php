<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Verification - {{product}}</title>
    <link rel="stylesheet" href="{{ asset('css/license-widget.css') }}">
</head>
<body data-license-api-url="{{license_api_url}}" data-product-slug="{{product_slug}}" data-verification-key="{{verification_key}}">
    <div class="license-verifier">
        <h1>License Verification</h1>
        <p>Product: <strong>{{product}}</strong></p>
        <p>API URL: <strong>{{license_api_url}}</strong></p>
        <p>API Token: <strong>{{api_token}}</strong></p>
        <p>Envato Token: <strong>{{envato_token}}</strong></p>

        <form id="licenseForm">
            <div class="form-group">
                <label for="purchaseCode">Purchase Code:</label>
                <input type="text" id="purchaseCode" name="purchaseCode" required
                       placeholder="Enter your purchase code">
            </div>

            <div class="form-group">
                <label for="domain">Domain (optional):</label>
                <input type="url" id="domain" name="domain"
                       placeholder="https://yourdomain.com">
            </div>

            <button type="submit" id="verifyBtn">
                <span id="btnText">Verify License</span>
                <span id="loading" class="loading" aria-hidden="true"></span>
            </button>
        </form>

        <div id="result" class="result"></div>
    </div>

    <script src="{{ asset('assets/front/js/license-config.js') }}"></script>
    <script src="{{ asset('assets/front/js/user-dashboard.js') }}"></script>
</body>
</html>