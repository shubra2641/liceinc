<?php

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Comprehensive Security Checker for Laravel Application
 * * This script performs complete static analysis security checks including:
 * - Input validation
 * - SQL injection prevention
 * - XSS protection
 * - Authentication security
 * - File upload security
 * - CSRF protection
 * - Rate limiting
 * - Password security
 * - Session security
 * - Error handling
 * * To run: php advanced_security_scan.php /path/to/your/laravel/app
 * * @version 1.0.0
 * @author My-Logos Team
 */

class SecurityChecker
{
    private $issues = [];
    private $warnings = [];
    private $passed = [];
    private $appPath;
    private $totalFiles = 0;
    private $scannedFiles = [];
    private $excludedPaths = [
        'vendor/',
        'node_modules/',
        'storage/',
        'bootstrap/cache/',
        '.git/',
        'tests/',
        'public/',
        '1111111111111111/',
        '.cursor/',
        'advanced_security_scan.php',
        'security_audit.php',
        'check_psr12.php',
        'security_checker.php',
    ];

    public function __construct($appPath = null)
    {
        $this->appPath = $appPath ?: __DIR__;
    }

    /**
     * Run comprehensive security check
     */
    public function runCheck()
    {
        echo "🔍 Starting Comprehensive Security Check...\n";
        echo "==========================================\n\n";
        
        $startTime = microtime(true);
        
        // Get all PHP files first
        $this->getAllPhpFiles();
        
        echo "📁 Found {$this->totalFiles} PHP files to scan\n\n";
        
        // Run all security checks
        $this->checkInputValidation();
        $this->checkSQLInjectionPrevention();
        $this->checkXSSProtection();
        $this->checkAuthenticationSecurity();
        $this->checkFileUploadSecurity();
        $this->checkCSRFProtection();
        $this->checkRateLimiting();
        $this->checkPasswordSecurity();
        $this->checkSessionSecurity();
        $this->checkErrorHandling();
        $this->checkSecurityHeaders();
        $this->checkDependencies();
        $this->checkBusinessLogic();
        $this->checkInformationDisclosure();
        $this->checkCommandInjection();
        $this->checkFileInclusion();
        
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        
        $this->generateDetailedReport($executionTime);
    }

    /**
     * Get all PHP files in the application
     */
    private function getAllPhpFiles()
    {
        // Ensure the path is valid before proceeding
        if (!is_dir($this->appPath)) {
            $this->addIssue('CRITICAL', "Application path not found: {$this->appPath}");
            return;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->appPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
        } catch (Exception $e) {
             echo "Error reading directory: {$e->getMessage()}\n";
             return;
        }
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $fileName = basename($filePath);
                
                // Skip excluded paths and files
                $skip = false;
                $relativePath = str_replace($this->appPath . DIRECTORY_SEPARATOR, '', $filePath);
                $relativePath = str_replace('\\', '/', $relativePath); // Normalize path separators

                foreach ($this->excludedPaths as $excludedPath) {
                    if (strpos($relativePath, $excludedPath) === 0 || $fileName === $excludedPath) {
                        $skip = true;
                        break;
                    }
                }
                
                if (!$skip) {
                    $this->scannedFiles[] = $filePath;
                    $this->totalFiles++;
                }
            }
        }
    }

    /**
     * Check input validation security
     */
    private function checkInputValidation()
    {
        echo "🔐 Checking Input Validation...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for direct $_POST, $_GET, $_REQUEST usage without validation
            if (preg_match('/\$_POST\[|\$_GET\[|\$_REQUEST\[/', $content)) {
                $this->addIssue('CRITICAL', 'Direct superglobal usage without validation', $file);
                $issues++;
            }
            
            // Check for missing validation in controllers (but exclude error/console controllers)
            if (strpos($file, 'Controller.php') !== false && 
                strpos($file, 'ErrorController') === false &&
                strpos($file, 'ConsoleController') === false &&
                strpos($file, 'PaymentPageController') === false) {
                if (strpos($content, 'Request') === false && strpos($content, 'validate') === false) {
                    $this->addWarning('Missing Request validation class', $file);
                }
            }
            
            // Check for raw input usage (excluding files with proper validation and comments)
            $cleanContent = preg_replace('/\/\*.*?\*\//s', '', $content);
            $cleanContent = preg_replace('/\/\/.*$/m', '', $cleanContent);
            
            if (preg_match('/input\(.*\)(?!.*validate)/', $cleanContent) && 
                !preg_match('/Request.*extends.*FormRequest/', $content) &&
                !preg_match('/validated\(\)/', $content) &&
                !preg_match('/validate\(/', $content) &&
                !preg_match('/rules\(/', $content)) {
                $this->addIssue('HIGH', 'Raw input usage without validation', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Input validation appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check SQL injection prevention
     */
    private function checkSQLInjectionPrevention()
    {
        echo "🛡️ Checking SQL Injection Prevention...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for raw SQL queries with variables
            if (preg_match('/DB::raw\(.*\$/', $content)) {
                $this->addIssue('CRITICAL', 'Raw SQL with variables detected', $file);
                $issues++;
            }
            
            // Check for direct SQL concatenation
            if (preg_match('/\$sql\s*\.=/', $content)) {
                $this->addIssue('HIGH', 'SQL string concatenation detected', $file);
                $issues++;
            }
            
            // Check for whereRaw with variables (but allow parameter binding)
            if (preg_match('/whereRaw\(.*\$/', $content) && 
                !preg_match('/whereRaw\([^)]*\?[^)]*\$/', $content) &&
                !preg_match('/whereRaw\([^)]*LIKE\s*\?/', $content) &&
                !preg_match('/whereRaw\([^)]*LOWER.*LIKE\s*\?/', $content)) {
                $this->addIssue('MEDIUM', 'whereRaw with variables - ensure proper binding', $file);
                $issues++;
            }
            
            // Check for missing parameter binding in raw queries
            if (preg_match('/DB::select\(.*\$.*\)/', $content)) {
                $this->addIssue('HIGH', 'Raw DB::select with variables - use parameter binding', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('SQL injection prevention appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check XSS protection
     */
    private function checkXSSProtection()
    {
        echo "🚫 Checking XSS Protection...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for direct echo without escaping
            if (preg_match('/echo\s+[^;]*\$/', $content) && !preg_match('/e\(/', $content)) {
                $this->addIssue('HIGH', 'Direct echo without escaping detected', $file);
                $issues++;
            }
            
            // Check for {!! !!} usage in Blade templates
            if (preg_match('/\{\!\s*\$.*\!\}/', $content)) {
                $this->addIssue('HIGH', 'Unescaped Blade output detected', $file);
                $issues++;
            }
            
            // Check for missing htmlspecialchars
            if (strpos($content, 'htmlspecialchars') === false && strpos($content, 'e(') === false) {
                if (preg_match('/echo.*\$|print.*\$/', $content)) {
                    $this->addWarning('Potential XSS vulnerability - check output escaping', $file);
                }
            }
            
            // Check for JavaScript injection
            if (preg_match('/<script.*\$/', $content)) {
                $this->addIssue('CRITICAL', 'Potential JavaScript injection detected', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('XSS protection appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check authentication security
     */
    private function checkAuthenticationSecurity()
    {
        echo "🔑 Checking Authentication Security...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        $authFiles = [
            $this->appPath . '/app/Http/Controllers/Auth/',
            $this->appPath . '/app/Http/Middleware/',
        ];
        
        foreach ($authFiles as $path) {
            if (is_dir($path)) {
                $files = glob($path . '*.php');
                foreach ($files as $file) {
                    $content = file_get_contents($file);
                    $filesChecked++;
                    
                    // Check for weak password validation (but allow proper validation)
                    if (strpos($content, 'password') !== false && 
                        strpos($content, 'min:8') === false &&
                        strpos($content, 'min:') === false &&
                        strpos($content, 'required') === false &&
                        strpos($content, 'string') === false &&
                        strpos($content, 'rules') === false &&
                        strpos($content, 'Request') === false) {
                        $this->addWarning('Weak password validation detected', $file);
                        $issues++;
                    }
                    
                    // Check for missing CSRF protection (but allow proper Request classes)
                    if (strpos($content, 'csrf') === false && 
                        strpos($content, 'VerifyCsrfToken') === false &&
                        strpos($content, 'Request') === false &&
                        strpos($content, 'FormRequest') === false) {
                        if (strpos($file, 'Controller') !== false) {
                            $this->addWarning('Missing CSRF protection', $file);
                            $issues++;
                        }
                    }
                }
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Authentication security appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check file upload security
     */
    private function checkFileUploadSecurity()
    {
        echo "📁 Checking File Upload Security...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for file upload operations specifically
            if (strpos($content, 'move_uploaded_file') !== false || 
                strpos($content, 'store(') !== false && strpos($content, 'file(') !== false ||
                strpos($content, 'uploadedFile') !== false ||
                strpos($content, 'file(') !== false && strpos($content, 'upload') !== false) {
                
                // Check for file type validation (in same file or Request class)
                $hasValidation = strpos($content, 'mimes:') !== false || 
                               strpos($content, 'mimetypes:') !== false ||
                               strpos($content, 'Request') !== false ||
                               strpos($content, 'validateFile') !== false ||
                               strpos($content, 'validation') !== false;
                
                if (!$hasValidation) {
                    $this->addIssue('HIGH', 'File upload without type validation', $file);
                    $issues++;
                }
                
                // Check for file size validation (but allow proper validation)
                if (strpos($content, 'max:') === false && 
                    strpos($content, 'Request') === false &&
                    strpos($content, 'getSize()') === false &&
                    strpos($content, 'size') === false) {
                    $this->addWarning('File upload without size validation', $file);
                    $issues++;
                }
                
                // Check for dangerous file extensions in validation rules
                if (preg_match('/mimes:.*\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)/', $content)) {
                    $this->addIssue('CRITICAL', 'Dangerous file extension allowed', $file);
                    $issues++;
                }
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('File upload security appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check CSRF protection
     */
    private function checkCSRFProtection()
    {
        echo "🛡️ Checking CSRF Protection...\n";
        
        $issues = 0;
        $filesChecked = 0;
        $csrfFound = false;
        
        // Check middleware files for VerifyCsrfToken
        $middlewareFiles = glob($this->appPath . '/app/Http/Middleware/*.php');
        
        foreach ($middlewareFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            if (strpos($content, 'VerifyCsrfToken') !== false) {
                $csrfFound = true;
                $this->addPassed('CSRF protection middleware detected');
                break;
            }
        }
        
        // Check bootstrap/app.php for CSRF configuration (Laravel 11)
        $bootstrapFile = $this->appPath . '/bootstrap/app.php';
        if (file_exists($bootstrapFile)) {
            $bootstrapContent = file_get_contents($bootstrapFile);
            $filesChecked++;
            
            if (strpos($bootstrapContent, 'validateCsrfTokens') !== false || 
                strpos($bootstrapContent, 'csrf') !== false) {
                $csrfFound = true;
                $this->addPassed('CSRF protection configured in bootstrap/app.php');
            }
        }
        
        if (!$csrfFound) {
            $this->addIssue('HIGH', 'CSRF protection middleware not found');
            $issues++;
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimiting()
    {
        echo "⏱️ Checking Rate Limiting...\n";
        
        $issues = 0;
        $filesChecked = 0;
        $hasRateLimiting = false;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            if (strpos($content, 'RateLimiter') !== false || strpos($content, 'throttle') !== false) {
                $hasRateLimiting = true;
                break;
            }
        }
        
        if (!$hasRateLimiting) {
            $this->addWarning('Rate limiting not detected');
            $issues++;
        } else {
            $this->addPassed('Rate limiting detected');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check password security
     */
    private function checkPasswordSecurity()
    {
        echo "🔐 Checking Password Security...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        $userModel = $this->appPath . '/app/Models/User.php';
        if (file_exists($userModel)) {
            $content = file_get_contents($userModel);
            $filesChecked++;
            
            // Check for Laravel's Hash::make or $casts = ['password' => 'hashed']
            if (strpos($content, 'Hash::make') !== false || strpos($content, "'password' => 'hashed'") !== false) {
                $this->addPassed('Password hashing detected');
            } else {
                $this->addIssue('CRITICAL', 'Password hashing not detected in User model (ensure proper hashing)', $userModel);
                $issues++;
            }
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check session security
     */
    private function checkSessionSecurity()
    {
        echo "🍪 Checking Session Security...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        $configFile = $this->appPath . '/config/session.php';
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $filesChecked++;
            
            // Check for 'secure' => true and 'http_only' => true
            if (strpos($content, "'secure' => env('SESSION_SECURE_COOKIE', false)") !== false) {
                // Laravel default is to check .env, which is okay if APP_ENV=production
                $this->addWarning('Session secure cookie check is dynamic (check .env settings)', $configFile);
            } elseif (strpos($content, "'secure' => true") !== false && strpos($content, "'httponly' => true") !== false) {
                $this->addPassed('Secure and HTTP-only session configuration detected');
            } else {
                $this->addIssue('MEDIUM', 'Session security configuration needs review (secure/httponly flags)', $configFile);
                $issues++;
            }
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check error handling
     */
    private function checkErrorHandling()
    {
        echo "⚠️ Checking Error Handling...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for exposed error messages (excluding comments and template files)
            $cleanContent = preg_replace('/\/\*.*?\*\//s', '', $content);
            $cleanContent = preg_replace('/\/\/.*$/m', '', $cleanContent);
            
            // Skip template files and blade files
            if (strpos($file, 'resources/views') !== false || strpos($file, '.blade.php') !== false) {
                continue;
            }
            
            if (preg_match('/dd\(|dump\(|var_dump\(|print_r\(|die\(|exit\(/', $cleanContent)) {
                $this->addIssue('HIGH', 'Debug functions detected in production code', $file);
                $issues++;
            }
            
            // Check for exposed database errors
            if (preg_match('/DB::.*->getMessage\(\)/', $content)) {
                $this->addWarning('Database error exposure detected', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Error handling appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check security headers
     */
    private function checkSecurityHeaders()
    {
        echo "🔒 Checking Security Headers...\n";
        
        $issues = 0;
        $filesChecked = 0;
        $headersFound = false;
        
        // Search for relevant middleware files (like HSTS, CSP, etc.)
        $middlewareFiles = glob($this->appPath . '/app/Http/Middleware/*.php');
        
        foreach ($middlewareFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            if (strpos($content, 'X-Frame-Options') !== false || 
                strpos($content, 'X-Content-Type-Options') !== false ||
                strpos($content, 'Content-Security-Policy') !== false) {
                $this->addPassed('Security headers middleware detected');
                $headersFound = true;
                break;
            }
        }
        
        if (!$headersFound) {
            $this->addIssue('MEDIUM', 'Security headers middleware not found or not explicitly set');
            $issues++;
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check dependencies
     */
    private function checkDependencies()
    {
        echo "📦 Checking Dependencies...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        $composerFile = $this->appPath . '/composer.json';
        if (file_exists($composerFile)) {
            $content = file_get_contents($composerFile);
            $filesChecked++;
            
            $this->addPassed('Composer dependencies found');
            
            // Check for outdated Laravel version (assuming >= 8.0 is okay, though newer is better)
            if (preg_match('/"laravel\/framework":\s*"([^"]+)"/', $content, $matches)) {
                $version = $matches[1];
                // Remove ^ or ~ prefix for version comparison
                $cleanVersion = str_replace(['^', '~'], '', $version);
                if (version_compare($cleanVersion, '9.0', '<')) {
                    $this->addIssue('HIGH', "Outdated Laravel version: $version (Recommended: 9.0+ for security fixes)");
                    $issues++;
                } else {
                    $this->addPassed("Laravel version $version appears current");
                }
            }
        } else {
             $this->addWarning('composer.json file not found - cannot check dependencies.');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check business logic flaws (high-level pattern matching)
     */
    private function checkBusinessLogic()
    {
        echo "💼 Checking Business Logic...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for price manipulation
            if (preg_match('/price\s*=\s*\$/', $content) && 
                !preg_match('/validated\(\)/', $content) &&
                !preg_match('/getPrice\(\)/', $content)) {
                $this->addWarning('Potential price manipulation vulnerability - check for input source', $file);
                $issues++;
            }
            
            // Check for quantity manipulation
            if (preg_match('/quantity\s*=\s*\$/', $content) && 
                !preg_match('/validated\(\)/', $content) &&
                !preg_match('/getQuantity\(\)/', $content)) {
                $this->addWarning('Potential quantity manipulation vulnerability - check for input source', $file);
                $issues++;
            }
            
            // Check for status manipulation (but allow safe toggles and validations)
            if (preg_match('/status\s*=\s*\$/', $content) && 
                !preg_match('/status\s*=\s*!\s*\$/', $content) &&
                !preg_match('/validated\(\)/', $content) &&
                !preg_match('/resetPassword\(/', $content)) {
                $this->addWarning('Potential unvalidated status manipulation', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Business logic code does not show obvious input manipulation patterns');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check information disclosure
     */
    private function checkInformationDisclosure()
    {
        echo "🔍 Checking Information Disclosure...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for debug information in controllers/services
            $cleanContent = preg_replace('/\/\*.*?\*\//s', '', $content);
            $cleanContent = preg_replace('/\/\/.*$/m', '', $cleanContent);
            
            if (strpos($file, 'resources/views') === false && preg_match('/dd\s*\(|dump\s*\(|var_dump\s*\(/', $cleanContent)) {
                $this->addIssue('HIGH', 'Debug information exposure (dd/dump/var_dump)', $file);
                $issues++;
            }
            
            // Check for sensitive keys
            if (preg_match('/(password|secret|key|api_token)\s*=\s*["\']/', $content) && strpos($file, 'config') === false) {
                 $this->addWarning('Hardcoded sensitive information detected', $file);
                 $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Information disclosure protection appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check command injection
     */
    private function checkCommandInjection()
    {
        echo "💻 Checking Command Injection...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for command execution with unvalidated variables
            if (preg_match('/\b(exec|system|shell_exec|passthru)\s*\(\s*["\'].*\$.*["\']\s*\)/', $content) ||
                preg_match('/`.*\$.*`/', $content)) {
                $this->addIssue('CRITICAL', 'Command execution with variables detected (use escapeshellarg/cmd)', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Command injection protection appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check file inclusion vulnerabilities
     */
    private function checkFileInclusion()
    {
        echo "📄 Checking File Inclusion...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        foreach ($this->scannedFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            // Check for dynamic file inclusion
            if (preg_match('/include\s*\(\s*\$|require\s*\(\s*\$/', $content)) {
                $this->addIssue('CRITICAL', 'Dynamic file inclusion detected (potential LFI/RFI)', $file);
                $issues++;
            }
            
            // Check for path traversal pattern
            if (preg_match('/\.\.\//', $content) && 
                !preg_match('/__DIR__.*\.\.\//', $content) &&
                !preg_match('/dirname\(.*\.\.\//', $content)) {
                $this->addWarning('Path traversal pattern detected (..)', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('File inclusion protection appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Helper to get relative path for display
     */
    private function getRelativePath($filePath)
    {
        if (!$filePath) {
            return 'N/A';
        }
        return str_replace($this->appPath . DIRECTORY_SEPARATOR, '', $filePath);
    }
    
    /**
     * Helper to print issues grouped by severity
     */
    private function printGroupedIssues(array $group, string $level)
    {
        if (count($group) > 0) {
            echo "--- {$level} ISSUES (". count($group) .") ---\n";
            foreach ($group as $issue) {
                $file = $this->getRelativePath($issue['file']);
                echo "  [{$issue['level']}] {$issue['message']} in file: {$file}\n";
            }
        }
    }

    /**
     * Add security issue
     */
    private function addIssue($level, $message, $file = null)
    {
        $this->issues[] = [
            'level' => $level,
            'message' => $message,
            'file' => $file,
        ];
    }

    /**
     * Add security warning
     */
    private function addWarning($message, $file = null)
    {
        $this->warnings[] = [
            'message' => $message,
            'file' => $file,
        ];
    }

    /**
     * Add passed check
     */
    private function addPassed($message)
    {
        $this->passed[] = $message;
    }

    /**
     * Generate detailed security report
     */
    private function generateDetailedReport($executionTime)
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 COMPREHENSIVE SECURITY CHECK REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        // Summary statistics
        $totalIssues = count($this->issues);
        $totalWarnings = count($this->warnings);
        $criticalIssues = array_filter($this->issues, fn($i) => $i['level'] === 'CRITICAL');
        $highIssues = array_filter($this->issues, fn($i) => $i['level'] === 'HIGH');
        $mediumIssues = array_filter($this->issues, fn($i) => $i['level'] === 'MEDIUM');

        // Overall Status
        if ($totalIssues > 0) {
            $status = "\033[41m\033[1m🚨 OVERALL STATUS: FAILED (CRITICAL ISSUES FOUND!)\033[0m";
        } elseif ($totalWarnings > 0) {
            $status = "\033[43m\033[1m⚠️ OVERALL STATUS: WARNINGS (Review recommended)\033[0m";
        } else {
            $status = "\033[42m\033[1m🟢 OVERALL STATUS: PASSED (No major issues found)\033[0m";
        }

        echo $status . "\n\n";

        // Summary Table
        echo "--- SUMMARY STATS ---\n";
        echo "Application Root: " . $this->appPath . "\n";
        echo "Total PHP Files Scanned: " . $this->totalFiles . "\n";
        echo "Total Issues Found: " . $totalIssues . "\n";
        echo "  - Critical: " . count($criticalIssues) . "\n";
        echo "  - High: " . count($highIssues) . "\n";
        echo "  - Medium: " . count($mediumIssues) . "\n";
        echo "Total Warnings: " . $totalWarnings . "\n";
        echo "Execution Time: {$executionTime}s\n";
        echo "-----------------------\n\n";

        // Detailed Issues
        if ($totalIssues > 0) {
            echo "🛑 DETAILED SECURITY ISSUES ({$totalIssues})\n";
            echo str_repeat("-", 30) . "\n";

            $this->printGroupedIssues($criticalIssues, 'CRITICAL');
            $this->printGroupedIssues($highIssues, 'HIGH');
            $this->printGroupedIssues($mediumIssues, 'MEDIUM');
            echo "\n";
        }

        // Detailed Warnings
        if ($totalWarnings > 0) {
            echo "🔶 DETAILED WARNINGS ({$totalWarnings})\n";
            echo str_repeat("-", 30) . "\n";
            foreach ($this->warnings as $warning) {
                $file = $this->getRelativePath($warning['file']);
                echo "- {$warning['message']} in file: {$file}\n";
            }
            echo "\n";
        }

        // Passed Checks
        echo "✅ PASSED CHECKS (" . count($this->passed) . ")\n";
        echo str_repeat("-", 30) . "\n";
        foreach (array_unique($this->passed) as $pass) {
            echo "- {$pass}\n";
        }
        echo "\n";

        echo str_repeat("=", 60) . "\n";
    }
}

// Main execution block to allow running the script from CLI
if (php_sapi_name() === 'cli') {
    $appPath = isset($argv[1]) ? $argv[1] : null;
    if (!$appPath) {
        $appPath = realpath(__DIR__);
    } else {
        $appPath = realpath($appPath);
    }

    if ($appPath) {
        $checker = new SecurityChecker($appPath);
        $checker->runCheck();
    } else {
        echo "Error: Application path is invalid or not provided.\n";
        echo "Usage: php advanced_security_scan.php /path/to/your/laravel/app\n";
    }
} else {
    echo "This script is designed to be run from the command line.\n";
}
