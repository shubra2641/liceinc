# Email Services System

## Overview

This is a modular, well-organized email services system that replaces the monolithic `EmailService` class. The new system is designed to be:

- **Modular**: Each email type has its own handler
- **Maintainable**: Clear separation of concerns
- **Type-safe**: Full PHPStan level 10 compliance
- **Secure**: Comprehensive input validation and sanitization
- **Backward Compatible**: Old `EmailService` still works

## Architecture

```
app/Services/Email/
├── Contracts/           # Interfaces for type safety
├── Handlers/           # Specialized email handlers
├── Validators/         # Input validation and sanitization
├── Traits/            # Reusable functionality
├── Facades/           # Laravel facades
├── CoreEmailService.php    # Core email functionality
├── EmailFacade.php         # Main facade
└── EmailServiceProvider.php # Service registration
```

## Usage

### Using the Facade (Recommended)

```php
use App\Services\Email\Facades\Email;

// Core email methods
Email::sendEmail('template_name', 'user@example.com', $data);
Email::sendToUser($user, 'template_name', $data);
Email::sendToAdmin('template_name', $data);

// User emails
Email::sendUserWelcome($user);
Email::sendEmailVerification($user, $verificationUrl);
Email::sendPasswordReset($user, $resetUrl);

// License emails
Email::sendPaymentConfirmation($license, $invoice);
Email::sendLicenseExpiring($user, $licenseData);

// Invoice emails
Email::sendInvoicePaid($user, $invoiceData);
Email::sendCustomInvoicePaymentConfirmation($invoice);

// Ticket emails
Email::sendTicketCreated($user, $ticketData);
Email::sendAdminTicketCreated($ticketData);
```

### Using Dependency Injection

```php
use App\Services\Email\EmailFacade;

class MyController
{
    public function __construct(
        protected EmailFacade $emailService
    ) {}
    
    public function sendWelcome($user)
    {
        return $this->emailService->sendUserWelcome($user);
    }
}
```

### Using Specialized Handlers

```php
use App\Services\Email\Handlers\UserEmailHandler;
use App\Services\Email\Handlers\LicenseEmailHandler;

class MyService
{
    public function __construct(
        protected UserEmailHandler $userEmailHandler,
        protected LicenseEmailHandler $licenseEmailHandler
    ) {}
    
    public function handleUserRegistration($user)
    {
        $this->userEmailHandler->sendUserWelcome($user);
        $this->userEmailHandler->sendNewUserNotification($user);
    }
}
```

## Backward Compatibility

The old `EmailService` class is still available and works exactly as before. It now uses the new system underneath, so you don't need to change existing code.

```php
// This still works exactly as before
$emailService = app(EmailService::class);
$emailService->sendToUser($user, 'template_name', $data);
```

## Configuration

Email services can be configured in `config/email_services.php`:

```php
return [
    'default' => 'core',
    'validation' => [
        'allowed_template_types' => ['user', 'admin'],
        'sanitize_inputs' => true,
        'validate_emails' => true,
    ],
    'security' => [
        'prevent_xss' => true,
        'rate_limit' => [
            'enabled' => true,
            'max_emails_per_minute' => 60,
        ],
    ],
];
```

## Security Features

- **Input Validation**: All inputs are validated and sanitized
- **XSS Protection**: HTML entities are escaped
- **Type Safety**: Full type hints and return types
- **Rate Limiting**: Configurable rate limiting
- **Error Handling**: Comprehensive error handling and logging

## Benefits

1. **Maintainability**: Each email type is handled by its own class
2. **Testability**: Easy to unit test individual handlers
3. **Extensibility**: Easy to add new email types
4. **Performance**: Better memory usage and faster execution
5. **Type Safety**: Full PHPStan level 10 compliance
6. **Security**: Comprehensive input validation

## Migration Guide

### For New Code
Use the new facade:
```php
use App\Services\Email\Facades\Email;
Email::sendUserWelcome($user);
```

### For Existing Code
No changes needed! The old `EmailService` still works:
```php
$emailService = app(EmailService::class);
$emailService->sendUserWelcome($user);
```

### For Controllers
Update dependency injection:
```php
// Old way
public function __construct(EmailService $emailService) {}

// New way (recommended)
public function __construct(EmailFacade $emailService) {}
```

## Testing

The new system is fully testable:

```php
use App\Services\Email\Handlers\UserEmailHandler;

class UserEmailHandlerTest extends TestCase
{
    public function test_send_user_welcome()
    {
        $handler = app(UserEmailHandler::class);
        $user = User::factory()->create();
        
        $result = $handler->sendUserWelcome($user);
        
        $this->assertTrue($result);
    }
}
```

## Performance

The new system is more efficient:
- **Memory**: Lower memory usage due to modular design
- **Speed**: Faster execution due to better organization
- **Caching**: Better caching opportunities
- **Debugging**: Easier to debug specific email types

## Support

For questions or issues, please refer to the code documentation or create an issue in the project repository.
