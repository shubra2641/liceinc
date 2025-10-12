<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Services\Email\Contracts\EmailServiceInterface;
use App\Services\Email\Contracts\EmailValidatorInterface;
use App\Services\Email\Facades\Email;
use App\Services\Email\Handlers\InvoiceEmailHandler;
use App\Services\Email\Handlers\LicenseEmailHandler;
use App\Services\Email\Handlers\TicketEmailHandler;
use App\Services\Email\Handlers\UserEmailHandler;
use App\Services\Email\Validators\EmailValidator;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

/**
 * Email Service Provider.
 *
 * Registers email services and their dependencies.
 *
 * @version 1.0.0
 */
class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register validator
        $this->app->singleton(EmailValidatorInterface::class, EmailValidator::class);

        // Register core email service
        $this->app->singleton(EmailServiceInterface::class, CoreEmailService::class);

        // Register specialized handlers
        $this->app->singleton(UserEmailHandler::class);
        $this->app->singleton(LicenseEmailHandler::class);
        $this->app->singleton(InvoiceEmailHandler::class);
        $this->app->singleton(TicketEmailHandler::class);

        // Register facade
        $this->app->singleton(EmailFacade::class);
        $this->app->alias(EmailFacade::class, 'Email');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Boot logic if needed
    }
}
