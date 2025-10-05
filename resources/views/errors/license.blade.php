<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>License Verification Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/errors/css/license-error.css') }}">
</head>
<body>
    <div class="license-error-card">
        <div class="license-error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h1 class="license-error-title">License Verification Required</h1>
        <p class="license-error-message">
            {{ $message ?? 'Your license needs to be verified to access this application.' }}
        </p>
        <p class="text-muted small mb-4">
            Please contact support if you believe this is an error.
        </p>
        <a href="{{ route('install.license') }}" class="btn btn-primary">
            <i class="fas fa-key me-2"></i>
            Verify License
        </a>
    </div>
</body>
</html>
