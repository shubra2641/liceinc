<?php

declare(strict_types=1);

namespace App\Services\Update;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * File Processing Service
 * 
 * Handles file processing operations for updates
 */
class FileProcessingService
{
    /**
     * Process uploaded file
     */
    public function processUploadedFile(UploadedFile $file, string $destinationPath): array
    {
        try {
            $this->validateFile($file);
            
            $filename = $this->generateFilename($file);
            $filePath = $this->storeFile($file, $destinationPath, $filename);
            
            $fileInfo = $this->extractFileInfo($filePath);
            
            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => $filename,
                'file_info' => $fileInfo
            ];
            
        } catch (\Exception $e) {
            Log::error('File processing failed', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract package contents
     */
    public function extractPackage(string $packagePath, string $extractPath): array
    {
        try {
            $this->validatePackage($packagePath);
            
            $extractedFiles = $this->performExtraction($packagePath, $extractPath);
            
            return [
                'success' => true,
                'extracted_files' => $extractedFiles,
                'extract_path' => $extractPath
            ];
            
        } catch (\Exception $e) {
            Log::error('Package extraction failed', [
                'package_path' => $packagePath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Invalid file upload');
        }
        
        $allowedMimes = ['application/zip', 'application/x-zip-compressed'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('File must be a ZIP archive');
        }
        
        $maxSize = 100 * 1024 * 1024; // 100MB
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size');
        }
    }

    /**
     * Generate filename
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "update_{$timestamp}.{$extension}";
    }

    /**
     * Store file
     */
    private function storeFile(UploadedFile $file, string $destinationPath, string $filename): string
    {
        $fullPath = $destinationPath . '/' . $filename;
        $file->storeAs($destinationPath, $filename, 'private');
        return $fullPath;
    }

    /**
     * Extract file information
     */
    private function extractFileInfo(string $filePath): array
    {
        $fullPath = Storage::disk('private')->path($filePath);
        
        return [
            'size' => filesize($fullPath),
            'mime_type' => mime_content_type($fullPath),
            'created_at' => filectime($fullPath),
            'modified_at' => filemtime($fullPath)
        ];
    }

    /**
     * Validate package
     */
    private function validatePackage(string $packagePath): void
    {
        $fullPath = Storage::disk('private')->path($packagePath);
        
        if (!file_exists($fullPath)) {
            throw new \InvalidArgumentException('Package file not found');
        }
        
        if (!is_readable($fullPath)) {
            throw new \InvalidArgumentException('Package file is not readable');
        }
    }

    /**
     * Perform extraction
     */
    private function performExtraction(string $packagePath, string $extractPath): array
    {
        $fullPath = Storage::disk('private')->path($packagePath);
        $extractFullPath = Storage::disk('private')->path($extractPath);
        
        $zip = new ZipArchive();
        $result = $zip->open($fullPath);
        
        if ($result !== TRUE) {
            throw new \InvalidArgumentException('Failed to open ZIP archive');
        }
        
        $extractedFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $extractedFiles[] = $filename;
        }
        
        $zip->extractTo($extractFullPath);
        $zip->close();
        
        return $extractedFiles;
    }
}
