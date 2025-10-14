<?php

declare(strict_types=1);

namespace App\Services\Update;

use Illuminate\Support\Facades\Storage;

/**
 * Package Validation Service
 * 
 * Handles validation for update packages
 */
class PackageValidationService
{
    /**
     * Validate package structure
     */
    public function validatePackageStructure(string $packagePath): array
    {
        try {
            $fullPath = Storage::disk('private')->path($packagePath);
            
            if (!file_exists($fullPath)) {
                return $this->createErrorResponse('Package file not found');
            }
            
            $zip = new \ZipArchive();
            $result = $zip->open($fullPath);
            
            if ($result !== TRUE) {
                return $this->createErrorResponse('Invalid ZIP archive');
            }
            
            $validationResult = $this->validateZipContents($zip);
            $zip->close();
            
            return $validationResult;
            
        } catch (\Exception $e) {
            return $this->createErrorResponse('Package validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate ZIP contents
     */
    private function validateZipContents(\ZipArchive $zip): array
    {
        $requiredFiles = ['index.php', 'composer.json'];
        $foundFiles = [];
        $errors = [];
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $foundFiles[] = $filename;
            
            // Check for dangerous files
            if ($this->isDangerousFile($filename)) {
                $errors[] = "Dangerous file detected: {$filename}";
            }
        }
        
        // Check for required files
        foreach ($requiredFiles as $requiredFile) {
            if (!in_array($requiredFile, $foundFiles)) {
                $errors[] = "Required file missing: {$requiredFile}";
            }
        }
        
        if (!empty($errors)) {
            return $this->createErrorResponse('Package validation failed', $errors);
        }
        
        return $this->createSuccessResponse([
            'total_files' => $zip->numFiles,
            'files' => $foundFiles
        ]);
    }

    /**
     * Check if file is dangerous
     */
    private function isDangerousFile(string $filename): bool
    {
        $dangerousExtensions = ['.exe', '.bat', '.cmd', '.scr', '.pif', '.com'];
        $dangerousFiles = ['php.ini', '.htaccess', 'web.config'];
        
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $basename = strtolower(basename($filename));
        
        return in_array($extension, $dangerousExtensions) || in_array($basename, $dangerousFiles);
    }

    /**
     * Validate package size
     */
    public function validatePackageSize(string $packagePath, int $maxSize = 100 * 1024 * 1024): bool
    {
        $fullPath = Storage::disk('private')->path($packagePath);
        $fileSize = filesize($fullPath);
        
        return $fileSize <= $maxSize;
    }

    /**
     * Validate package permissions
     */
    public function validatePackagePermissions(string $packagePath): bool
    {
        $fullPath = Storage::disk('private')->path($packagePath);
        
        return is_readable($fullPath) && is_writable($fullPath);
    }

    /**
     * Create success response
     */
    private function createSuccessResponse(array $data): array
    {
        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Create error response
     */
    private function createErrorResponse(string $message, array $errors = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }
}
