<?php
/**
 * Envato Compliance Checker
 * 
 * This script checks the system for Envato marketplace compliance issues
 * including CSS, JavaScript, and PHP standards.
 * 
 * @version 1.0.0
 * @author License Management System
 */

echo "🛡️  Envato Compliance Checker\n";
echo "================================\n\n";

$basePath = __DIR__;
$issues = [];
$filesScanned = 0;

/**
 * Scan directory for files
 */
function scanDirectory($dir, $extensions = []) {
    global $filesScanned;
    $files = [];
    
    if (!is_dir($dir)) return $files;
    
    // Excluded directories and files
    $excludedPaths = [
        'envato_compliance_checker.php',
        'check_psr12.php', 
        'security_audit.php',
        'security_checker.php',
        '.codacy/',
        '.cursor/',
        '1111111111111111/',
        '.github/',
        'storage/',
        'tests/',
        'vendor/',
        'node_modules/',
        'bootstrap/cache/',
        '.git/'
    ];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filePath = $file->getPathname();
            $relativePath = str_replace($dir . DIRECTORY_SEPARATOR, '', $filePath);
            $relativePath = str_replace('\\', '/', $relativePath);
            
            // Check if file should be excluded
            $shouldExclude = false;
            foreach ($excludedPaths as $excludedPath) {
                if (strpos($relativePath, $excludedPath) === 0 || 
                    basename($filePath) === $excludedPath ||
                    strpos($filePath, $excludedPath) !== false) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if (!$shouldExclude) {
                $ext = strtolower($file->getExtension());
                if (empty($extensions) || in_array($ext, $extensions)) {
                    $files[] = $file->getPathname();
                    $filesScanned++;
                }
            }
        }
    }
    
    return $files;
}

/**
 * Check CSS compliance
 */
function checkCSSCompliance($file) {
    global $issues;
    $content = file_get_contents($file);
    $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file);
    
    // Check for inline CSS in Blade files
    if (strpos($file, '.blade.php') !== false) {
        if (preg_match('/<style[^>]*>/i', $content)) {
            $issues[] = [
                'type' => 'CSS',
                'severity' => 'HIGH',
                'file' => $relativePath,
                'issue' => 'Inline CSS detected in Blade template',
                'solution' => 'Move CSS to external stylesheet'
            ];
        }
    }
    
    // Check for SVG/path in CSS (should use Font Awesome instead)
    if (preg_match('/url\([^)]*\.svg\)/i', $content)) {
        $issues[] = [
            'type' => 'CSS',
            'severity' => 'MEDIUM',
            'file' => $relativePath,
            'issue' => 'SVG icons detected in CSS',
            'solution' => 'Use Font Awesome (fas fa-*) instead of SVG'
        ];
    }
    
    // Check for inline elements containing block elements
    if (preg_match('/<(span|em|strong|small)[^>]*>.*?<(div|h[1-6]|p|section|article|header|footer|nav|main|aside)[^>]*>/is', $content)) {
        $issues[] = [
            'type' => 'CSS',
            'severity' => 'HIGH',
            'file' => $relativePath,
            'issue' => 'Block elements inside inline elements detected',
            'solution' => 'Restructure HTML to avoid block elements inside inline elements'
        ];
    }
    
    // Check for @push and @stack usage in Blade files
    if (strpos($file, '.blade.php') !== false) {
        if (preg_match('/@push\s*\([^)]+\)/', $content) || preg_match('/@stack\s*\([^)]+\)/', $content)) {
            $issues[] = [
                'type' => 'CSS',
                'severity' => 'MEDIUM',
                'file' => $relativePath,
                'issue' => '@push/@stack usage detected in Blade template',
                'solution' => 'Use external CSS/JS files and import in layout'
            ];
        }
    }
}

/**
 * Check JavaScript compliance
 */
function checkJavaScriptCompliance($file) {
    global $issues;
    $content = file_get_contents($file);
    $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file);
    
    // Check for @push and @stack in Blade files
    if (strpos($file, '.blade.php') !== false) {
        if (preg_match('/<script[^>]*>/i', $content)) {
            $issues[] = [
                'type' => 'JavaScript',
                'severity' => 'HIGH',
                'file' => $relativePath,
                'issue' => 'Inline JavaScript detected in Blade template',
                'solution' => 'Move JavaScript to external file and import in layout'
            ];
        }
    }
    
    // Check for progressive enhancement
    if (strpos($file, '.js') !== false) {
        // Check if JavaScript has fallback mechanisms
        if (preg_match('/addEventListener|onclick|onload/i', $content)) {
            if (!preg_match('/noscript|fallback|degraded/i', $content)) {
                $issues[] = [
                    'type' => 'JavaScript',
                    'severity' => 'MEDIUM',
                    'file' => $relativePath,
                    'issue' => 'JavaScript may lack progressive enhancement',
                    'solution' => 'Add fallback mechanisms for when JavaScript is disabled'
                ];
            }
        }
    }
}

/**
 * Check PHP compliance
 */
function checkPHPCompliance($file) {
    global $issues;
    $content = file_get_contents($file);
    $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file);
    
    // Check for Log::info usage
    if (preg_match('/Log::info\s*\(/i', $content)) {
        $issues[] = [
            'type' => 'PHP',
            'severity' => 'MEDIUM',
            'file' => $relativePath,
            'issue' => 'Log::info usage detected',
            'solution' => 'Remove Log::info, use Log::error for errors only'
        ];
    }
    
    // Check for @push and @stack in Blade files
    if (strpos($file, '.blade.php') !== false) {
        if (preg_match('/@push\s*\([^)]+\)/', $content) || preg_match('/@stack\s*\([^)]+\)/', $content)) {
            $issues[] = [
                'type' => 'PHP',
                'severity' => 'MEDIUM',
                'file' => $relativePath,
                'issue' => '@push/@stack usage detected in Blade template',
                'solution' => 'Use external CSS/JS files and import in layout'
            ];
        }
    }
    
    // Check for security measures
    if (strpos($file, 'Controller') !== false) {
        if (preg_match('/\$request->input\s*\(/', $content) && !preg_match('/validated\s*\(/', $content)) {
            $issues[] = [
                'type' => 'PHP',
                'severity' => 'HIGH',
                'file' => $relativePath,
                'issue' => 'Raw input usage without validation',
                'solution' => 'Use $request->validated() or Form Request classes'
            ];
        }
    }
}

/**
 * Check for documentation
 */
function checkDocumentation() {
    global $issues;
    
    $docFiles = [
        'README.md',
        'CHANGELOG.md',
        'LICENSE'
    ];
    
    foreach ($docFiles as $docFile) {
        if (!file_exists($docFile)) {
            $issues[] = [
                'type' => 'Documentation',
                'severity' => 'HIGH',
                'file' => $docFile,
                'issue' => 'Missing documentation file',
                'solution' => 'Create ' . $docFile . ' with proper documentation'
            ];
        }
    }
}

/**
 * Check for configuration files
 */
function checkConfiguration() {
    global $issues;
    
    $configFiles = [
        'config/app.php',
        'config/database.php',
        'config/mail.php'
    ];
    
    foreach ($configFiles as $configFile) {
        if (!file_exists($configFile)) {
            $issues[] = [
                'type' => 'Configuration',
                'severity' => 'HIGH',
                'file' => $configFile,
                'issue' => 'Missing configuration file',
                'solution' => 'Ensure all configuration files exist'
            ];
        }
    }
}

// Main execution
echo "🔍 Scanning files...\n";

// Scan CSS files
$cssFiles = scanDirectory($basePath, ['css']);
foreach ($cssFiles as $file) {
    checkCSSCompliance($file);
}

// Scan JavaScript files
$jsFiles = scanDirectory($basePath, ['js']);
foreach ($jsFiles as $file) {
    checkJavaScriptCompliance($file);
}

// Scan PHP files
$phpFiles = scanDirectory($basePath, ['php']);
foreach ($phpFiles as $file) {
    checkPHPCompliance($file);
}

// Check documentation
checkDocumentation();

// Check configuration
checkConfiguration();

// Display results
echo "📊 Scan Results:\n";
echo "Files scanned: $filesScanned\n";
echo "Issues found: " . count($issues) . "\n\n";

if (empty($issues)) {
    echo "✅ No compliance issues found! System is ready for Envato marketplace.\n";
} else {
    echo "❌ Compliance Issues Found:\n";
    echo "============================\n\n";
    
    $groupedIssues = [];
    foreach ($issues as $issue) {
        $groupedIssues[$issue['type']][] = $issue;
    }
    
    foreach ($groupedIssues as $type => $typeIssues) {
        echo "📁 $type Issues (" . count($typeIssues) . "):\n";
        echo str_repeat('-', 50) . "\n";
        
        foreach ($typeIssues as $issue) {
            $severity = strtoupper($issue['severity']);
            $severityIcon = $issue['severity'] === 'HIGH' ? '🔴' : ($issue['severity'] === 'MEDIUM' ? '🟡' : '🟢');
            
            echo "$severityIcon [$severity] {$issue['file']}\n";
            echo "   Issue: {$issue['issue']}\n";
            echo "   Solution: {$issue['solution']}\n\n";
        }
    }
}

// Generate detailed summary report
echo "\n" . str_repeat("=", 80) . "\n";
echo "📋 DETAILED ENVATO COMPLIANCE REPORT\n";
echo str_repeat("=", 80) . "\n\n";

// File scan summary
echo "📁 FILES SCANNED SUMMARY:\n";
echo str_repeat("-", 40) . "\n";
echo "Total Files Scanned: $filesScanned\n";
echo "Excluded Directories: .codacy/, .cursor/, 1111111111111111/, .github/, storage/, tests/, vendor/\n";
echo "Excluded Files: envato_compliance_checker.php, check_psr12.php, security_audit.php, security_checker.php\n\n";

// Issues summary by type
$issueCounts = [];
$severityCounts = ['HIGH' => 0, 'MEDIUM' => 0, 'LOW' => 0];

foreach ($issues as $issue) {
    $type = $issue['type'];
    $severity = $issue['severity'];
    
    if (!isset($issueCounts[$type])) {
        $issueCounts[$type] = 0;
    }
    $issueCounts[$type]++;
    $severityCounts[$severity]++;
}

echo "📊 ISSUES SUMMARY BY TYPE:\n";
echo str_repeat("-", 40) . "\n";
foreach ($issueCounts as $type => $count) {
    echo "$type: $count issues\n";
}
echo "\n";

echo "📊 ISSUES SUMMARY BY SEVERITY:\n";
echo str_repeat("-", 40) . "\n";
echo "🔴 HIGH: " . $severityCounts['HIGH'] . " issues\n";
echo "🟡 MEDIUM: " . $severityCounts['MEDIUM'] . " issues\n";
echo "🟢 LOW: " . $severityCounts['LOW'] . " issues\n";
echo "Total: " . count($issues) . " issues\n\n";

// Compliance status
$totalIssues = count($issues);
$highIssues = $severityCounts['HIGH'];

if ($totalIssues === 0) {
    echo "✅ ENVATO COMPLIANCE STATUS: PASSED\n";
    echo "🎉 System is ready for Envato marketplace submission!\n";
} elseif ($highIssues === 0) {
    echo "⚠️ ENVATO COMPLIANCE STATUS: NEEDS REVIEW\n";
    echo "🔧 Fix medium and low priority issues before submission.\n";
} else {
    echo "❌ ENVATO COMPLIANCE STATUS: FAILED\n";
    echo "🚨 Critical issues must be resolved before submission.\n";
}

echo "\n🏁 Scan completed!\n";
echo "For Envato compliance, ensure all issues are resolved before submission.\n";
?>
