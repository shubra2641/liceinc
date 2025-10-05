<?php

/**
 * Comprehensive Security Checker for Laravel Application
 * 
 * This script performs complete security analysis including:
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
 * 
 * @version 1.0.0
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
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->appPath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $fileName = basename($filePath);
                
                // Skip excluded paths and files
                $skip = false;
                
                // Check for specific files first
                foreach ($this->excludedPaths as $excludedPath) {
                    if (strpos($excludedPath, '/') === false) {
                        if ($fileName === $excludedPath) {
                            $skip = true;
                            break;
                        }
                    }
                }
                
                // Check for directory paths
                if (!$skip) {
                    foreach ($this->excludedPaths as $excludedPath) {
                        if (strpos($excludedPath, '/') !== false) {
                            if (strpos($filePath, $excludedPath) !== false) {
                                $skip = true;
                                break;
                            }
                        }
                    }
                }
                
                // Additional hard-coded exclusions
                if (!$skip) {
                    $relativePath = str_replace($this->appPath . DIRECTORY_SEPARATOR, '', $filePath);
                    $relativePath = str_replace('\\', '/', $relativePath);
                    
                    // Skip vendor directory
                    if (strpos($relativePath, 'vendor/') === 0) {
                        $skip = true;
                    }
                    // Skip node_modules directory
                    elseif (strpos($relativePath, 'node_modules/') === 0) {
                        $skip = true;
                    }
                    // Skip storage directory
                    elseif (strpos($relativePath, 'storage/') === 0) {
                        $skip = true;
                    }
                    // Skip public directory
                    elseif (strpos($relativePath, 'public/') === 0) {
                        $skip = true;
                    }
                    // Skip tests directory
                    elseif (strpos($relativePath, 'tests/') === 0) {
                        $skip = true;
                    }
                    // Skip bootstrap/cache directory
                    elseif (strpos($relativePath, 'bootstrap/cache/') === 0) {
                        $skip = true;
                    }
                    // Skip .git directory
                    elseif (strpos($relativePath, '.git/') === 0) {
                        $skip = true;
                    }
                    // Skip .cursor directory
                    elseif (strpos($relativePath, '.cursor/') === 0) {
                        $skip = true;
                    }
                    // Skip 1111111111111111 directory
                    elseif (strpos($relativePath, '1111111111111111/') === 0) {
                        $skip = true;
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
            
            // Check for missing validation in controllers
            if (strpos($file, 'Controller.php') !== false) {
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
                    
                    // Check for weak password validation
                    if (strpos($content, 'password') !== false && strpos($content, 'min:8') === false) {
                        $this->addWarning('Weak password validation detected', $file);
                        $issues++;
                    }
                    
                    // Check for missing CSRF protection
                    if (strpos($content, 'csrf') === false && strpos($content, 'VerifyCsrfToken') === false) {
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
                
                // Check for file size validation
                if (strpos($content, 'max:') === false && strpos($content, 'Request') === false) {
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
            
            if (strpos($content, 'hashed') !== false) {
                $this->addPassed('Password hashing detected');
            } else {
                $this->addIssue('CRITICAL', 'Password hashing not detected in User model');
                $issues++;
            }
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
            
            if (strpos($content, 'secure') !== false && strpos($content, 'true') !== false) {
                $this->addPassed('Secure session configuration detected');
            } else {
                $this->addWarning('Session security configuration needs review');
                $issues++;
            }
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
            if (strpos($file, 'templates') !== false || strpos($file, 'blade.php') !== false) {
                continue;
            }
            
            if (preg_match('/dd\(|dump\(|var_dump\(|Console\.WriteLine|console\.log/', $cleanContent)) {
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
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check security headers
     */
    private function checkSecurityHeaders()
    {
        echo "🔒 Checking Security Headers...\n";
        
        $issues = 0;
        $filesChecked = 0;
        
        $middlewareFiles = glob($this->appPath . '/app/Http/Middleware/*.php');
        
        foreach ($middlewareFiles as $file) {
            $content = file_get_contents($file);
            $filesChecked++;
            
            if (strpos($content, 'X-Frame-Options') !== false || 
                strpos($content, 'X-Content-Type-Options') !== false ||
                strpos($content, 'X-XSS-Protection') !== false) {
                $this->addPassed('Security headers middleware detected');
                break;
            }
        }
        
        if ($filesChecked === 0 || !isset($content) || strpos($content, 'X-Frame-Options') === false) {
            $this->addIssue('MEDIUM', 'Security headers middleware not found');
            $issues++;
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
            
            // Check for outdated Laravel version
            if (preg_match('/"laravel\/framework":\s*"([^"]+)"/', $content, $matches)) {
                $version = $matches[1];
                if (version_compare($version, '8.0', '<')) {
                    $this->addIssue('HIGH', "Outdated Laravel version: $version");
                    $issues++;
                } else {
                    $this->addPassed("Laravel version $version appears current");
                }
            }
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
    }

    /**
     * Check business logic flaws
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
            if (preg_match('/price\s*=\s*\$/', $content)) {
                $this->addIssue('HIGH', 'Price manipulation vulnerability', $file);
                $issues++;
            }
            
            // Check for quantity manipulation
            if (preg_match('/quantity\s*=\s*\$/', $content)) {
                $this->addIssue('HIGH', 'Quantity manipulation vulnerability', $file);
                $issues++;
            }
            
            // Check for status manipulation (but allow safe toggles and validations)
            if (preg_match('/status\s*=\s*\$/', $content) && 
                !preg_match('/status\s*=\s*!\s*\$/', $content) &&
                !preg_match('/status\s*=\s*\$.*\?.*:/', $content) &&
                !preg_match('/validated\(\)/', $content) &&
                !preg_match('/resetPassword\(/', $content) &&
                !preg_match('/Password::/', $content)) {
                $this->addIssue('MEDIUM', 'Status manipulation vulnerability', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Business logic appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
            
            // Check for debug information (excluding comments and template files)
            $cleanContent = preg_replace('/\/\*.*?\*\//s', '', $content);
            $cleanContent = preg_replace('/\/\/.*$/m', '', $cleanContent);
            
            // Skip template files and blade files
            if (strpos($file, 'templates') !== false || strpos($file, 'blade.php') !== false) {
                continue;
            }
            
            if (preg_match('/dd\s*\(|dump\s*\(|var_dump\s*\(/', $cleanContent)) {
                $this->addIssue('HIGH', 'Debug information exposure', $file);
                $issues++;
            }
            
            // Check for error exposure (but allow proper error handling)
            if (preg_match('/->getMessage\s*\(\s*\)/', $content) && 
                !preg_match('/Log::/', $content) &&
                !preg_match('/catch\s*\(/', $content) &&
                !preg_match('/return.*error/', $content)) {
                $this->addWarning('Error message exposure detected', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Information disclosure protection appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
            
            // Check for direct command execution (excluding curl_exec)
            if (preg_match('/\b(exec|system|shell_exec|passthru)\s*\(\s*\$/', $content) && 
                !preg_match('/curl_exec/', $content)) {
                $this->addIssue('CRITICAL', 'Command execution with variables detected', $file);
                $issues++;
            }
            
            // Check for backtick execution (excluding template literals and comments)
            // Remove comments first
            $cleanContent = preg_replace('/\/\*.*?\*\//s', '', $content);
            $cleanContent = preg_replace('/\/\/.*$/m', '', $cleanContent);
            
            if (preg_match('/`.*\$.*`/', $cleanContent) && 
                !preg_match('/template|javascript|js|react/', $file)) {
                $this->addIssue('CRITICAL', 'Backtick execution with variables detected', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('Command injection protection appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
            
            // Check for direct file inclusion
            if (preg_match('/include\s*\(\s*\$|require\s*\(\s*\$/', $content)) {
                $this->addIssue('CRITICAL', 'Dynamic file inclusion detected', $file);
                $issues++;
            }
            
            // Check for path traversal (but allow safe Laravel usage)
            if (preg_match('/\.\.\//', $content) && 
                !preg_match('/__DIR__.*\.\.\//', $content) &&
                !preg_match('/routes.*\.\.\//', $content)) {
                $this->addIssue('HIGH', 'Path traversal pattern detected', $file);
                $issues++;
            }
        }
        
        if ($issues === 0) {
            $this->addPassed('File inclusion protection appears to be properly implemented');
        }
        
        echo "   ✅ Files checked: $filesChecked\n";
        echo "   ❌ Issues found: $issues\n\n";
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
        $totalPassed = count($this->passed);
        
        echo "📈 SCAN STATISTICS:\n";
        echo "   Total Files Scanned: {$this->totalFiles}\n";
        echo "   Execution Time: {$executionTime} seconds\n";
        echo "   Total Issues: $totalIssues\n";
        echo "   Total Warnings: $totalWarnings\n";
        echo "   Passed Checks: $totalPassed\n\n";
        
        // Critical issues
        $critical = array_filter($this->issues, fn($issue) => $issue['level'] === 'CRITICAL');
        if (!empty($critical)) {
            echo "🚨 CRITICAL ISSUES (" . count($critical) . "):\n";
            foreach ($critical as $issue) {
                echo "   ❌ " . $issue['message'];
                if ($issue['file']) {
                    echo " in " . basename($issue['file']);
                }
                echo "\n";
            }
            echo "\n";
        }
        
        // High issues
        $high = array_filter($this->issues, fn($issue) => $issue['level'] === 'HIGH');
        if (!empty($high)) {
            echo "⚠️ HIGH ISSUES (" . count($high) . "):\n";
            foreach ($high as $issue) {
                echo "   ⚠️ " . $issue['message'];
                if ($issue['file']) {
                    echo " in " . basename($issue['file']);
                }
                echo "\n";
            }
            echo "\n";
        }
        
        // Medium issues
        $medium = array_filter($this->issues, fn($issue) => $issue['level'] === 'MEDIUM');
        if (!empty($medium)) {
            echo "🔶 MEDIUM ISSUES (" . count($medium) . "):\n";
            foreach ($medium as $issue) {
                echo "   🔶 " . $issue['message'];
                if ($issue['file']) {
                    echo " in " . basename($issue['file']);
                }
                echo "\n";
            }
            echo "\n";
        }
        
        // Warnings
        if (!empty($this->warnings)) {
            echo "⚠️ WARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   ⚠️ " . $warning['message'];
                if ($warning['file']) {
                    echo " in " . basename($warning['file']);
                }
                echo "\n";
            }
            echo "\n";
        }
        
        // Passed checks
        if (!empty($this->passed)) {
            echo "✅ PASSED CHECKS (" . count($this->passed) . "):\n";
            foreach ($this->passed as $pass) {
                echo "   ✅ " . $pass . "\n";
            }
            echo "\n";
        }
        
        // Final assessment
        echo str_repeat("=", 60) . "\n";
        if ($totalIssues === 0) {
            echo "🎉 EXCELLENT! No security issues found!\n";
            echo "Your application appears to be secure.\n";
        } elseif ($totalIssues <= 5) {
            echo "✅ GOOD! Few security issues found.\n";
            echo "Please address the issues above to improve security.\n";
        } elseif ($totalIssues <= 15) {
            echo "⚠️ MODERATE! Several security issues found.\n";
            echo "Please address the issues above to improve security.\n";
        } else {
            echo "🚨 CRITICAL! Many security issues found.\n";
            echo "Please address the issues above immediately to improve security.\n";
        }
        echo str_repeat("=", 60) . "\n";
        
        // Save report to file
        $this->saveReportToFile($executionTime);
    }

    /**
     * Save report to file
     */
    private function saveReportToFile($executionTime)
    {
        $report = "Comprehensive Security Check Report\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $report .= "Execution Time: {$executionTime} seconds\n";
        $report .= "Total Files Scanned: {$this->totalFiles}\n";
        $report .= str_repeat("=", 50) . "\n\n";
        
        $report .= "CRITICAL ISSUES:\n";
        foreach (array_filter($this->issues, fn($issue) => $issue['level'] === 'CRITICAL') as $issue) {
            $report .= "- " . $issue['message'] . ($issue['file'] ? " in " . $issue['file'] : "") . "\n";
        }
        
        $report .= "\nHIGH ISSUES:\n";
        foreach (array_filter($this->issues, fn($issue) => $issue['level'] === 'HIGH') as $issue) {
            $report .= "- " . $issue['message'] . ($issue['file'] ? " in " . $issue['file'] : "") . "\n";
        }
        
        $report .= "\nMEDIUM ISSUES:\n";
        foreach (array_filter($this->issues, fn($issue) => $issue['level'] === 'MEDIUM') as $issue) {
            $report .= "- " . $issue['message'] . ($issue['file'] ? " in " . $issue['file'] : "") . "\n";
        }
        
        $report .= "\nWARNINGS:\n";
        foreach ($this->warnings as $warning) {
            $report .= "- " . $warning['message'] . ($warning['file'] ? " in " . $warning['file'] : "") . "\n";
        }
        
        $report .= "\nPASSED CHECKS:\n";
        foreach ($this->passed as $pass) {
            $report .= "- " . $pass . "\n";
        }
        
        file_put_contents($this->appPath . '/security_check_report.txt', $report);
        echo "\n📄 Detailed report saved to: security_check_report.txt\n";
    }
}

// Run the security check
if (php_sapi_name() === 'cli') {
    $checker = new SecurityChecker();
    $checker->runCheck();
} else {
    echo "This script should be run from the command line.\n";
    echo "Usage: php security_checker.php\n";
}
