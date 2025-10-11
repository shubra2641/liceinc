<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $rendered['subject'] }}</title>

</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ config('app.name') }}</h1>
            <p>{{ $rendered['subject'] }}</p>
        </div>

        <!-- Content -->
        <div class="email-content">
            {{ $rendered['body'] }}
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>
                <strong>{{ config('app.name') }}</strong><br>
                {{ config('app.url') }}
            </p>

            <div class="social-links">
                <a href="{{ config('app.url') }}/support">Support</a>
                <a href="{{ config('app.url') }}/privacy">Privacy Policy</a>
                <a href="{{ config('app.url') }}/terms">Terms of Service</a>
            </div>

            <p class="text-xs text-gray-400">
                This email was sent to {{ $data['recipient_email'] ?? 'you' }}.
                If you no longer wish to receive these emails, you can
                <a href="{{ config('app.url') }}/unsubscribe">unsubscribe here</a>.
            </p>

            <p class="text-xs text-gray-400">
                © {{ $data['current_year'] ?? date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>