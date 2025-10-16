<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\SecurityHelper;
use Illuminate\Database\DatabaseManager;

/**
 * Invoice Service Container
 * 
 * Manages all dependencies for InvoiceService to reduce coupling.
 */
class InvoiceServiceContainer
{
    private InvoiceValidationHelper $validationHelper;
    private InvoiceSanitizationHelper $sanitizationHelper;
    private InvoiceLoggingHelper $loggingHelper;
    private InvoiceOperationsHelper $operationsHelper;

    public function __construct()
    {
        $this->validationHelper = new InvoiceValidationHelper();
        $this->sanitizationHelper = new InvoiceSanitizationHelper();
        $this->loggingHelper = new InvoiceLoggingHelper();
        $this->operationsHelper = new InvoiceOperationsHelper();
    }

    public function getValidationHelper(): InvoiceValidationHelper
    {
        return $this->validationHelper;
    }

    public function getSanitizationHelper(): InvoiceSanitizationHelper
    {
        return $this->sanitizationHelper;
    }

    public function getLoggingHelper(): InvoiceLoggingHelper
    {
        return $this->loggingHelper;
    }

    public function getOperationsHelper(): InvoiceOperationsHelper
    {
        return $this->operationsHelper;
    }
}
