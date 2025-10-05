<?php
/**
 * PSR-12 Compliance Checker.
 *
 * Simple script to check PHP files for PSR-12 compliance
 * Excludes vendor/, tests/, and storage/ directories
 *
 */
class check_psr12
{
    private $excludedDirs = ['vendor', 'tests', 'storage', 'database', 'bootstrap', 'resources', 'node_modules'];
    private $issues = [];
    private $checkedFiles = 0;
    private $totalIssues = 0;
    private $autoFix = false;
    private $backupDir = null;
    public function __construct($autoFix = false)
    {
        $this->autoFix = $autoFix;
        echo '🔍 PSR-12 Compliance Checker';
        if ($this->autoFix) {
            echo ' (AUTO-FIX MODE - USE WITH CAUTION!)';
            $this->backupDir = 'psr12_backups_'.date('Y-m-d_H-i-s');
            mkdir($this->backupDir);
            echo "\n📁 Backups will be saved to: {$this->backupDir}";
        }
        echo "\n================================\n";
        if ($this->autoFix) {
            $this->showAutoFixWarnings();
        }
    }
    /**
     * Start the checking process.
     */
    public function check()
    {
        $this->scanDirectory('.');
        $this->displayResults();
    }
    /**
     * Show critical warnings for auto-fix mode.
     */
    private function showAutoFixWarnings()
    {
        echo "\n🚨 CRITICAL WARNINGS FOR AUTO-FIX MODE:\n";
        echo str_repeat('=', 60)."\n";
        echo "❌ AUTO-FIX MODE CAN BREAK YOUR APPLICATION!\n";
        echo "❌ ONLY USE IN DEVELOPMENT ENVIRONMENT!\n";
        echo "❌ NEVER USE IN PRODUCTION!\n";
        echo "❌ BACKUP YOUR CODE BEFORE PROCEEDING!\n";
        echo "❌ REVIEW ALL CHANGES AFTER AUTO-FIX!\n";
        echo str_repeat('=', 60)."\n\n";
        echo "What will be auto-fixed (SAFE changes only):\n";
        echo "✅ Remove trailing whitespace\n";
        echo "✅ Replace tabs with 4 spaces\n";
        echo "✅ Fix spacing after commas\n";
        echo "✅ Remove spaces before semicolons\n";
        echo "✅ Add spaces around assignment operators\n\n";
        echo "What will NOT be auto-fixed (POTENTIALLY DANGEROUS):\n";
        echo "❌ Breaking long lines (may break logic)\n";
        echo "❌ Changing line endings (may break on different OS)\n";
        echo "❌ Array syntax changes (may break compatibility)\n";
        echo "❌ Brace placement changes (may break syntax)\n\n";
        echo "Press Ctrl+C now if you want to cancel...\n";
        sleep(3); // Give user time to cancel
        echo "Starting auto-fix process...\n\n";
    }
    /**
     * Scan directory recursively.
     */
    private function scanDirectory($dir)
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = $dir.DIRECTORY_SEPARATOR.$file;
            // Skip excluded directories
            if (is_dir($fullPath) && in_array($file, $this->excludedDirs)) {
                continue;
            }
            if (is_dir($fullPath)) {
                $this->scanDirectory($fullPath);
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $this->checkFile($fullPath);
            }
        }
    }
    /**
     * Check individual PHP file.
     */
    private function checkFile($filePath)
    {
        // Skip checking the checker script itself
        if (basename($filePath) === 'check_psr12.php') {
            return;
        }
        $this->checkedFiles++;
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }
        $originalContent = $content;
        $lines = explode("\n", $content);
        $fileIssues = [];
        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // 1-based line numbers
            // Check for common PSR-12 violations
            $issues = $this->checkLine($line, $lineNumber, $lines, $lineNumber - 2);
            if (! empty($issues)) {
                $fileIssues = array_merge($fileIssues, $issues);
            }
        }
        if (! empty($fileIssues)) {
            $this->issues[$filePath] = $fileIssues;
            $this->totalIssues += count($fileIssues);
            // Auto-fix if enabled
            if ($this->autoFix) {
                $this->autoFixFile($filePath, $lines, $fileIssues);
            }
        }
    }
    /**
     * Auto-fix PSR-12 issues in a file (SAFE fixes only).
     */
    private function autoFixFile($filePath, &$lines, $fileIssues)
    {
        $relativePath = str_replace(getcwd().DIRECTORY_SEPARATOR, '', $filePath);
        echo "🔧 Auto-fixing: {$relativePath}\n";
        $fixedLines = $lines;
        $fixesApplied = 0;
        foreach ($fileIssues as $issue) {
            $lineIndex = $issue['line'] - 1; // 0-based
            if (! isset($fixedLines[$lineIndex])) {
                continue;
            }
            $originalLine = $fixedLines[$lineIndex];
            switch ($issue['type']) {
                case 'Trailing Whitespace':
                    // Safe: Remove trailing whitespace
                    $fixedLines[$lineIndex] = rtrim($originalLine);
                    $fixesApplied++;
                    echo "  ✅ Fixed trailing whitespace on line {$issue['line']}\n";
                    break;
                case 'Tab Usage':
                    // Safe: Replace tabs with 4 spaces
                    $fixedLines[$lineIndex] = str_replace("\t", '    ', $originalLine);
                    $fixesApplied++;
                    echo "  ✅ Replaced tabs with spaces on line {$issue['line']}\n";
                    break;
                case 'Spacing':
                    if (strpos($issue['message'], 'Add space after comma') !== false) {
                        // Safe: Add space after comma
                        $fixedLines[$lineIndex] = preg_replace('/,([^ ])/', ', $1', $originalLine);
                        $fixesApplied++;
                        echo "  ✅ Added space after comma on line {$issue['line']}\n";
                    } elseif (strpos($issue['message'], 'Remove space before semicolon') !== false) {
                        // Safe: Remove space before semicolon
                        $fixedLines[$lineIndex] = preg_replace('/ +;$/', ';', $originalLine);
                        $fixesApplied++;
                        echo "  ✅ Removed space before semicolon on line {$issue['line']}\n";
                    }
                    break;
                case 'Operator Spacing':
                    if (strpos($issue['message'], 'Add spaces around assignment operators') !== false) {
                        // Safe: Add spaces around = operator
                        $fixedLines[$lineIndex] = preg_replace('/([^=!<>])=([^=])/', '$1 = $2', $originalLine);
                        $fixesApplied++;
                        echo "  ✅ Added spaces around assignment operator on line {$issue['line']}\n";
                    }
                    break;
                default:
                    // Skip potentially dangerous fixes
                    echo "  ⚠️  Skipped potentially dangerous fix: {$issue['type']} on line {$issue['line']}\n";
                    break;
            }
        }
        if ($fixesApplied > 0) {
            // Create backup
            $backupPath = $this->backupDir.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], '_', $relativePath);
            if (copy($filePath, $backupPath)) {
                echo "  📁 Backup created: {$backupPath}\n";
            }
            // Write fixed content
            $fixedContent = implode("\n", $fixedLines);
            if (file_put_contents($filePath, $fixedContent) !== false) {
                echo "  ✅ Applied {$fixesApplied} safe fixes\n";
            } else {
                echo "  ❌ Failed to write fixed content\n";
            }
        } else {
            echo "  ℹ️  No safe fixes were applicable\n";
        }
        echo "\n";
    }
    /**
     * Check individual line for PSR-12 violations.
     */
    private function checkLine($line, $lineNumber, $allLines = [], $prevLineIndex = -1)
    {
        $issues = [];
        // Skip empty files or lines
        if (empty($line) && count($allLines) <= 1) {
            return $issues;
        }
        $originalLine = $line;
        $trimmedLine = trim($line);
        // Skip lines that are inside multi-line comments, strings, or heredoc
        if ($this->isInsideMultiLineConstruct($line, $allLines, $lineNumber - 1)) {
            return $issues;
        }
        // 1. Check for trailing whitespace
        if (preg_match('/\s+$/', $originalLine) && ! preg_match('/^[\s]*$/', $originalLine)) {
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Trailing Whitespace',
                'message' => 'Remove trailing whitespace',
                'code' => 'PSR-12: No trailing whitespace',
            ];
        }
        // 2. Check for tabs (should use spaces)
        if (strpos($originalLine, "\t") !== false && ! preg_match('/^[\s]*$/', $originalLine)) {
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Tab Usage',
                'message' => 'Use spaces instead of tabs (4 spaces per indent)',
                'code' => 'PSR-12: Use 4 spaces for indentation',
            ];
        }
        // 3. Check for long lines (over 120 characters) - but skip comments and strings
        if (strlen($originalLine) > 120 &&
            ! preg_match('/^\s*(\*|\/\/|#)/', $originalLine) && // Skip comments
            ! preg_match('/^\s*[\'"]/', $originalLine) && // Skip string starts
            ! preg_match('/[\'"]\s*,\s*$/', $originalLine)) { // Skip string concatenations
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Line Length',
                'message' => 'Line exceeds 120 characters ('.strlen($originalLine).' chars)',
                'code' => 'PSR-12: Lines should not exceed 120 characters',
            ];
        }
        // 4. Check for mixed line endings (CRLF)
        if (strpos($originalLine, "\r\n") !== false || strpos($originalLine, "\r") !== false) {
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Line Endings',
                'message' => 'Use LF line endings, not CRLF or CR',
                'code' => 'PSR-12: Use LF line endings',
            ];
        }
        // 5. Check for multiple consecutive blank lines (more than 2)
        if (empty($trimmedLine)) {
            $consecutiveEmpty = 1;
            $checkIndex = $lineNumber - 2; // 0-based index
            while ($checkIndex >= 0 && isset($allLines[$checkIndex]) && empty(trim($allLines[$checkIndex]))) {
                $consecutiveEmpty++;
                $checkIndex--;
            }
            if ($consecutiveEmpty > 2) {
                $issues[] = [
                    'line' => $lineNumber,
                    'type' => 'Multiple Blank Lines',
                    'message' => 'No more than 2 consecutive blank lines allowed',
                    'code' => 'PSR-12: Maximum 2 consecutive blank lines',
                ];
            }
        }
        // 6. Check for space before semicolon (only for statements, not for loops or expressions)
        if (preg_match('/\s+;$/', $trimmedLine) &&
            ! preg_match('/for\s*\([^;]+;\s*[^;]+;\s*[^;]+\)\s*;$/', $trimmedLine)) {
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Spacing',
                'message' => 'Remove space before semicolon',
                'code' => 'PSR-12: No space before semicolon',
            ];
        }
        // 7. Check for missing space after comma (in function calls, arrays, etc.)
        if (preg_match('/,[^\s\)\]\}]/', $trimmedLine) &&
            ! preg_match('/[\'"].*,.*[\'"]/', $trimmedLine)) { // Skip inside strings
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Spacing',
                'message' => 'Add space after comma',
                'code' => 'PSR-12: Add space after comma',
            ];
        }
        // 8. Check for space before opening parenthesis in function calls
        // Skip comments, strings, control structure keywords (including match), and arrow functions
        if (! preg_match('/^\s*(\/\/|#|\/\*|\*)/', $trimmedLine) && // Skip comments
            ! preg_match('/[\'\"].*\w\s+\(.*[\'\"]/', $trimmedLine) && // Skip inside strings
            preg_match('/\b[A-Za-z_][A-Za-z0-9_]*\s+\(/', $trimmedLine) && // word + space + (
            ! preg_match('/\b(if|elseif|while|for|foreach|switch|catch|match|fn|function|return|new)\s+\(/', $trimmedLine) && // exclude language constructs & declarations
            ! preg_match('/^declare\s*\(/', $trimmedLine) // Skip declare()
        ) {
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Spacing',
                'message' => 'Remove space before opening parenthesis in function calls',
                'code' => 'PSR-12: No space before opening parenthesis',
            ];
        }
        // 9. Check for array() syntax (should use [])
        if (preg_match('/\barray\s*\(/', $trimmedLine) &&
            ! preg_match('/\w+array\s*\(/', $trimmedLine) && // Skip function names ending with array
            ! preg_match('/\barray\s*\(\s*\)/', $trimmedLine)) { // Skip empty arrays (handled separately)
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Array Syntax',
                'message' => 'Use short array syntax [] instead of array()',
                'code' => 'PSR-12: Use short array syntax',
            ];
        }
        // 10. Check for proper brace placement for control structures (multi-line aware)
        if (preg_match('/^(if|elseif|else|while|for|foreach|switch|try|catch|finally)\b/', $trimmedLine)) {
            // Gather multi-line condition until closing parenthesis balance reached
            $conditionLines = [$trimmedLine];
            $openParens = substr_count($trimmedLine, '(') - substr_count($trimmedLine, ')');
            $scanIndex = $lineNumber; // 1-based
            while ($openParens > 0 && isset($allLines[$scanIndex])) {
                $next = trim($allLines[$scanIndex]);
                $conditionLines[] = $next;
                $openParens += substr_count($next, '(') - substr_count($next, ')');
                $scanIndex++;
                if ($scanIndex - $lineNumber > 20) { // safety break
                    break;
                }
            }
            // Last collected line
            $lastCondLine = end($conditionLines);
            if (!preg_match('/\)\s*\{\s*$/', $lastCondLine)) {
                // If brace on separate line is allowed? PSR-12 requires brace on same line for control structures.
                // Look ahead to first non-empty line after condition; if it starts with { then flag.
                $firstAfterIndex = $scanIndex - 1; // current scanIndex is first line after condition
                while (isset($allLines[$firstAfterIndex]) && trim($allLines[$firstAfterIndex]) === '') {
                    $firstAfterIndex++;
                }
                if (isset($allLines[$firstAfterIndex]) && preg_match('/^\s*\{/', $allLines[$firstAfterIndex])) {
                    $issues[] = [
                        'line' => $lineNumber,
                        'type' => 'Brace Placement',
                        'message' => 'Opening brace should be on the same line for control structures',
                        'code' => 'PSR-12: Opening brace on same line',
                    ];
                }
            }
        }
        // 11. Check for proper spacing around operators
        // Check for assignment operators without proper spacing, but exclude array syntax =>
            if (! preg_match('/^declare\s*\(/', $trimmedLine) && // Do not flag declare(strict_types=1)
                // Skip compound assignment operators like /=, +=, -=, .=, *=, etc.
                !preg_match('/([+\-*\/.%]|\.)(=)/', $trimmedLine) &&
                (preg_match('/([^=!<>\s])=\s*([^=\s>])/', $trimmedLine) || // No space before =
                 preg_match('/([^=!<>\s])\s*=([^=\s>])/', $trimmedLine)) && // No space after =
            ! preg_match('/[\'"].*=.*[\'"]/', $trimmedLine) && // Exclude inside strings
            ! preg_match('/=>\s*$/', $trimmedLine)) { // Exclude array syntax ending with =>
            $issues[] = [
                'line' => $lineNumber,
                'type' => 'Operator Spacing',
                'message' => 'Add spaces around assignment operators',
                'code' => 'PSR-12: Spaces around operators',
            ];
        }
        return $issues;
    }
    /**
     * Get backup directory path.
     */
    public function getBackupDir()
    {
        return $this->backupDir;
    }
    /**
     * Check if line is inside multi-line construct (comment, string, heredoc).
     */
    private function isInsideMultiLineConstruct($line, $allLines, $currentIndex)
    {
        static $inMultiLineComment = false;
        static $inString = false;
        static $stringChar = '';
        static $inHeredoc = false;
        static $heredocLabel = '';
        // Check for multi-line comment start/end
        if (strpos($line, '/*') !== false && strpos($line, '*/') === false) {
            $inMultiLineComment = true;
        }
        if (strpos($line, '*/') !== false) {
            $inMultiLineComment = false;
        }
        // Check for string start/end
        if (! $inString) {
            if (preg_match('/^[^\'"]*[\'"]/', $line, $matches)) {
                $inString = true;
                $quotePos = strpos($line, $matches[0][strlen($matches[0]) - 1]);
                $stringChar = $line[$quotePos];
            }
        } else {
            if (strpos($line, $stringChar) !== false &&
                substr_count($line, '\\'.$stringChar) % 2 === 0) { // Not escaped
                $inString = false;
                $stringChar = '';
            }
        }
        // Check for heredoc
        if (preg_match('/<<<(\w+)/', $line, $matches)) {
            $inHeredoc = true;
            $heredocLabel = $matches[1];
        }
        if ($inHeredoc && trim($line) === $heredocLabel) {
            $inHeredoc = false;
            $heredocLabel = '';
        }
        return $inMultiLineComment || $inString || $inHeredoc;
    }
    /**
     * Display results.
     */
    private function displayResults()
    {
        echo "🔍 PSR-12 Compliance Checker\n";
        echo "================================\n";
        echo "📁 Files checked: {$this->checkedFiles}\n";
        echo "❌ Total issues: {$this->totalIssues}\n";
        echo '📂 Files with issues: '.count($this->issues)."\n\n";
        if (empty($this->issues)) {
            echo "✅ Congratulations! No PSR-12 violations found.\n";
            echo "🎉 All checked files are PSR-12 compliant!\n";
            return;
        }
        echo "🚨 PSR-12 VIOLATIONS FOUND:\n";
        echo str_repeat('=', 60)."\n\n";
        foreach ($this->issues as $file => $fileIssues) {
            $relativePath = str_replace(getcwd().DIRECTORY_SEPARATOR, '', $file);
            echo "📄 File: .\\{$relativePath}\n";
            echo str_repeat('-', strlen($relativePath) + 9)."\n";
            foreach ($fileIssues as $issue) {
                echo "  Line {$issue['line']}: {$issue['type']}\n";
                echo "  📝 {$issue['message']}\n";
                echo "  📋 {$issue['code']}\n\n";
            }
        }
        echo str_repeat('=', 60)."\n";
        echo "📊 DETAILED SUMMARY\n";
        echo str_repeat('=', 60)."\n";
        // Group issues by type
        $issueTypes = [];
        foreach ($this->issues as $fileIssues) {
            foreach ($fileIssues as $issue) {
                $type = $issue['type'];
                if (! isset($issueTypes[$type])) {
                    $issueTypes[$type] = 0;
                }
                $issueTypes[$type]++;
            }
        }
        echo "� Issues by type:\n";
        foreach ($issueTypes as $type => $count) {
            echo "  • {$type}: {$count}\n";
        }
        echo "\n🔧 REQUIRED FIXES\n";
        echo str_repeat('=', 60)."\n";
        echo "This script only detects issues. Manual fixes are required.\n\n";
        echo "Common PSR-12 fixes:\n";
        echo "• Remove trailing whitespace from lines\n";
        echo "• Replace tabs with 4 spaces for indentation\n";
        echo "• Break long lines (120+ characters) into multiple lines\n";
        echo "• Use LF line endings (Unix style), not CRLF (Windows)\n";
        echo "• Use short array syntax [] instead of array()\n";
        echo "• Add proper spacing after commas in function calls\n";
        echo "• Remove spaces before semicolons\n";
        echo "• Add spaces around assignment operators (=, +=, etc.)\n";
        echo "• Place opening braces on the same line for control structures\n";
        echo "• Limit consecutive blank lines to maximum 2\n\n";
        echo "💡 Pro tip: Use an IDE with PSR-12 formatting rules enabled\n";
        echo "   to automatically fix most of these issues.\n\n";
    }
}
// Run the checker
if (php_sapi_name() === 'cli') {
    $autoFix = false;
    // Check for --fix argument
    if (isset($argv[1]) && $argv[1] === '--fix') {
        $autoFix = true;
    }
    $checker = new check_psr12($autoFix);
    $checker->check();
    if ($autoFix) {
        echo "\n🎯 Auto-fix completed!\n";
        echo "📋 Review all changes and test your application thoroughly.\n";
        echo "🗂️  Backups are saved in: {$checker->getBackupDir()}\n";
    }
} else {
    echo "This script must be run from the command line.\n";
    echo "Usage: php check_psr12.php [--fix]\n";
    echo "  --fix    Enable auto-fix mode (USE WITH EXTREME CAUTION!)\n";
}
