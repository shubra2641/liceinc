<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Helpers\SecureFileHelper;
use Illuminate\Support\Facades\Log;

class LicenseExportService
{
    public function exportToCsv(): array
    {
        try {
            $licenses = License::with(['user', 'product'])->get();
            $filename = 'licenses_' . date('Y-m-d_H-i-s') . '.csv';
            
            return [
                'success' => true,
                'filename' => $filename,
                'licenses' => $licenses,
                'headers' => $this->getCsvHeaders(),
            ];
        } catch (\Exception $e) {
            Log::error('License export failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Failed to export licenses: ' . $e->getMessage(),
            ];
        }
    }

    public function getCsvHeaders(): array
    {
        return [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $this->getFilename() . '"',
        ];
    }

    public function getFilename(): string
    {
        return 'licenses_' . date('Y-m-d_H-i-s') . '.csv';
    }

    public function generateCsvCallback($licenses): callable
    {
        return function () use ($licenses) {
            $file = SecureFileHelper::openOutput('w');
            if (!is_resource($file)) {
                return;
            }

            // CSV Headers
            fputcsv($file, [
                'ID',
                'License Key',
                'User',
                'Product',
                'Status',
                'Max Domains',
                'Expires At',
                'Created At',
            ]);

            // CSV Data
            foreach ($licenses as $license) {
                fputcsv($file, [
                    $license->id,
                    $license->license_key,
                    $license->user->name ?? 'N/A',
                    $license->product->name ?? 'N/A',
                    $license->status,
                    $license->max_domains,
                    $license->expires_at,
                    $license->created_at,
                ]);
            }

            SecureFileHelper::closeFile($file);
        };
    }
}
