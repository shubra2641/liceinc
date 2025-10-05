<?php
/**
 * Application Security Static Auditor (Enhanced)
 *
 * - Original checks (superglobals, SQL concat, raw SQL, blade unescaped, dangerous funcs, unserialize, base64, CSRF, hardcoded secrets, insecure http, path traversal)
 * - Added: JavaScript DOM injection checks (innerHTML, insertAdjacentHTML, document.write),
 *          detection of eval usages in JS contexts,
 *          detection of echo/print of PHP variables into HTML without htmlspecialchars,
 *          checks for missing input filtering (filter_input/filter_var not used),
 *          small improvements to file scanning/extensions.
 *
 * Usage:
 *  php security_audit.php [--json] [--path=relative/path] [--max-files=5000]
 *
 * Notes:
 *  - Static heuristics will have false positives; review manually.
 *  - You can silence a specific line with: // security-ignore: RULE_CODE
 */
class SecurityAudit
{
    private array $excludedDirs = ['vendor','storage','node_modules','bootstrap','database','resources/lang','tests/Fixtures'];
    private array $excludedFilePatterns = ['*.min.js','*.lock','composer/*.php','.env','.env*'];
    private array $issues = [];
    private int $checkedFiles = 0;
    private int $totalIssues = 0;
    private bool $json = false;
    private bool $countsOnly = false;
    private ?string $basePath = null;
    private int $maxFiles = 8000;

    /** @var array<string,array> */
    private array $rules;

    public function __construct(bool $json = false, ?string $basePath = null, int $maxFiles = 8000, bool $countsOnly = false)
    {
        $this->json = $json;
        $this->basePath = $basePath ?: getcwd();
        $this->maxFiles = $maxFiles;
        $this->countsOnly = $countsOnly;
        $this->bootstrapRules();
        if (! $this->json && ! $this->countsOnly) {
            echo "🛡️  Security Static Auditor (Enhanced)\n";
            echo "================================\n";
            echo "Base Path: {$this->basePath}\n";
        }
    }

    private function bootstrapRules(): void
    {
        $this->rules = [
            'RAW_SUPERGLOBAL' => [
                'pattern' => '/\$_(GET|POST|REQUEST|COOKIE|FILES|SERVER)\b/',
                'severity' => 'LOW',
                'message' => 'Raw superglobal access; prefer Request facade / dependency-injected Request or filter_input.',
            ],
            'SQL_STRING_CONCAT' => [
                'pattern' => '/\b(SELECT|INSERT|UPDATE|DELETE)\b(?=[^A-Za-z](?:[^;\n]{0,120}?\b(FROM|WHERE|SET|INTO)\b|[^;\n]{0,5}?"|[^;\n]{0,5}?\'))[^;\n]*\.(\$|\$_)/i',
                'severity' => 'HIGH',
                'message' => 'Possible SQL injection via string concatenation.',
            ],
            'PDO_QUERY_CONCAT' => [
                'pattern' => '/->query\(.*\.(\$|\$_).*\)/i',
                'severity' => 'HIGH',
                'message' => 'PDO::query with concatenated input (use prepared statements).',
            ],
            'LARAVEL_RAW' => [
                'pattern' => '/(DB::raw|->raw\(|selectRaw\(|whereRaw\(|havingRaw\(|orderByRaw\()/i',
                'severity' => 'MEDIUM',
                'message' => 'Raw SQL expression; ensure bindings / sanitization.',
            ],
            'BLADE_UNESCAPED' => [
                'pattern' => '/{!!.*!!}/',
                'severity' => 'HIGH',
                'message' => 'Unescaped Blade echo (potential XSS).',
            ],
            'DIRECT_ECHO_USER_INPUT' => [
                'pattern' => '/echo\s+\$?_*(GET|POST|REQUEST)\b/',
                'severity' => 'HIGH',
                'message' => 'Direct echo of user input.',
            ],
            'ECHO_NO_HTML_ESCAPE' => [
                // Simple heuristic: echo of a variable (or print) not wrapped in htmlspecialchars() or e() helper (Laravel)
                // Dash moved to end of character class or escaped to avoid invalid range warnings.
                'pattern' => '/(?:echo|print)\s+(?!htmlspecialchars\b|htmlentities\b|e\()(\$[A-Za-z0-9_>\[\]\'\"-]+)/i',
                'severity' => 'HIGH',
                'message' => 'Outputting PHP variable without escaping (use htmlspecialchars / e() in Blade).',
                // We'll check context later to reduce false positives (e.g., CLI scripts)
            ],
            'DANGEROUS_FUNC' => [
                'pattern' => '/\b(eval|exec|shell_exec|system|passthru|popen|proc_open|create_function)\s*\(/',
                'severity' => 'CRITICAL',
                'message' => 'Dangerous function usage.',
            ],
            'UNSERIALIZE' => [
                'pattern' => '/\bunserialize\s*\(/',
                'severity' => 'HIGH',
                'message' => 'unserialize() used; ensure allowed_classes => false.',
            ],
            'BASE64_USER_INPUT' => [
                'pattern' => '/base64_decode\s*\(\s*\$_(GET|POST|REQUEST)/i',
                'severity' => 'MEDIUM',
                'message' => 'Decoding raw user input; could hide payload.',
            ],
            'HARDCODED_SECRET' => [
                // Only flag APP_KEY if it actually contains a generated base64 key (avoid empty placeholder triggering CRITICAL)
                'pattern' => '/(APP_KEY=base64:[A-Za-z0-9+\/=]{32,}|sk_live_[0-9a-zA-Z]{10,}|-----BEGIN (RSA |EC )?PRIVATE KEY-----)/',
                'severity' => 'CRITICAL',
                'message' => 'Potential hardcoded secret (replace with placeholder or move to deployment-specific env).',
            ],
            'INSECURE_HTTP' => [
                'pattern' => '/(curl_init|file_get_contents|fopen)\(\s*[\'\"]http:\\/\//i',
                'severity' => 'MEDIUM',
                'message' => 'Insecure HTTP URL used (consider HTTPS).',
            ],
            'PATH_TRAVERSAL' => [
                'pattern' => '/(fopen|file_put_contents|unlink|rename)\s*\(\s*\$.*(\.\.|\/\.\.)/i',
                'severity' => 'HIGH',
                'message' => 'User-influenced path with traversal pattern.',
            ],
            'BLADE_FORM_NO_CSRF' => [
                'pattern' => '/<form[^>]*method=["\']post["\'][^>]*>(?![\s\S]*?(?:@csrf|csrf_field\()))/i',
                'severity' => 'HIGH',
                'message' => 'POST form missing CSRF token directive.',
                'fileFilter' => '/resources\\/views\\/.*\.blade\.php$/',
            ],
            // New JS/DOM injection rules
            'JS_DOM_INJECTION' => [
                // Detect innerHTML / outerHTML / insertAdjacentHTML usage that concatenates PHP variables or uses superglobals
                'pattern' => '/(innerHTML|outerHTML|insertAdjacentHTML|insertAdjacentElement)\s*=|\.insertAdjacentHTML\(/i',
                'severity' => 'HIGH',
                'message' => 'DOM HTML insertion method detected; ensure inserted content is sanitized/escaped.',
            ],
            'DOCUMENT_WRITE' => [
                'pattern' => '/document\.write\s*\(/i',
                'severity' => 'HIGH',
                'message' => 'document.write used; may lead to JS/HTML injection if fed by user input.',
            ],
            'JS_EVAL_USAGE' => [
                'pattern' => '/\beval\s*\(/i',
                'severity' => 'CRITICAL',
                'message' => 'eval() usage detected (JS/PHP); avoid dynamic eval of strings.',
            ],
            'UNSAFE_JS_WITH_PHP' => [
                // Detect patterns where PHP variables are injected into JS strings/HTML without escaping
                // e.g. echo "<script>var x = '{$user}'</script>";
                'pattern' => '/<script[^>]*>.*(\$\w+|\$_(GET|POST|REQUEST)).*<\/script>/is',
                'severity' => 'HIGH',
                'message' => 'PHP variable injected into <script> tag; escape/encode or pass via JSON safely.',
            ],
            'MISSING_FILTER_INPUT' => [
                // Heuristic: use of superglobals without filter_input/filter_var nearby
                'pattern' => '/\$_(GET|POST|REQUEST|COOKIE)\b/',
                'severity' => 'MEDIUM',
                'message' => 'Superglobal used; prefer filter_input / filter_var or validated Request object.',
            ],
        ];
    }

    public function run(): void
    {
        $this->scanDirectory($this->basePath);
        $this->report();
    }

    private function scanDirectory(string $dir): void
    {
        if (! is_dir($dir)) {return;}
        $items = scandir($dir);
        if ($items === false) {return;}
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {continue;}
            $full = $dir.DIRECTORY_SEPARATOR.$item;
            $rel = ltrim(str_replace($this->basePath, '', $full), DIRECTORY_SEPARATOR);

            if (is_dir($full)) {
                if ($this->isExcludedDir($rel)) {continue;}
                $this->scanDirectory($full);
                if ($this->checkedFiles >= $this->maxFiles) {return;}
            } else {
                if ($this->shouldScanFile($full, $rel)) {
                    $this->scanFile($full, $rel);
                    if ($this->checkedFiles >= $this->maxFiles) {return;}
                }
            }
        }
    }

    private function isExcludedDir(string $relative): bool
    {
        foreach ($this->excludedDirs as $ex) {
            if ($relative === $ex || str_starts_with($relative, rtrim($ex,'/').DIRECTORY_SEPARATOR)) {return true;}
        }
        return false;
    }

    private function shouldScanFile(string $fullPath, string $relative): bool
    {
        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
        // include .js, .vue maybe, and blade.php detection (extension 'php' catches blade.php too)
        if (! in_array($ext, ['php','js','env','stub','txt','conf','ini','yml','yaml'])) {return false;}
        $base = basename($fullPath);
        if (in_array($base, ['security_audit.php','check_psr12.php'])) {return false;}
        // Always skip minified JS to avoid noisy false positives (vendor libs)
        if ($ext === 'js' && str_contains($base, '.min.js')) {return false;}
        foreach ($this->excludedFilePatterns as $pattern) {
            $regex = '/^'.str_replace(['*','?'], ['.*','.?'], preg_quote($pattern,'/')).'$/';
            if (preg_match($regex, basename($relative))) {return false;}
        }
        return true;
    }

    private function scanFile(string $fullPath, string $relative): void
    {
        $content = @file_get_contents($fullPath);
        if ($content === false) {return;}
        $this->checkedFiles++;
        $lines = preg_split('/\r\n|\r|\n/', $content);
        foreach ($this->rules as $code => $rule) {
            if (isset($rule['fileFilter']) && !preg_match($rule['fileFilter'], str_replace('/', '\\', $relative))) {
                continue;
            }
            if (!preg_match_all($rule['pattern'], $content, $matches, PREG_OFFSET_CAPTURE)) {continue;}
            foreach ($matches[0] as $match) {
                $offset = $match[1];
                $lineNumber = $this->offsetToLine($content, $offset);
                $lineText = $lines[$lineNumber-1] ?? '';

                // Reduce false positives for certain rules
                if ($code === 'DANGEROUS_FUNC') {
                    $trim = ltrim($lineText);
                    if (str_starts_with($trim, '//') || str_starts_with($trim, '#') || str_starts_with($trim, '*') || str_starts_with($trim, '/*') || str_starts_with($trim, '*/')) {
                        continue;
                    }
                }

                if ($code === 'ECHO_NO_HTML_ESCAPE') {
                    // Try to reduce false positives: skip if file looks CLI (no HTML tags) OR variable passed to functions
                    if (!preg_match('/<[^>]+>/', $content) && !preg_match('/<script/', $content)) {
                        // likely not HTML output — skip
                        continue;
                    }
                    // also skip if htmlspecialchars used anywhere on same line
                    if (preg_match('/htmlspecialchars\s*\(/i', $lineText) || preg_match('/htmlentities\s*\(/i', $lineText) || preg_match('/\be\(/i', $lineText)) {
                        continue;
                    }
                }

                // For MISSING_FILTER_INPUT we try to avoid duplicate reports when RAW_SUPERGLOBAL already raised same line
                if ($code === 'MISSING_FILTER_INPUT') {
                    if ($this->isIgnoredLine($lineText, $code)) {continue;}
                    // If the same line contains filter_input or filter_var then skip
                    if (preg_match('/filter_input\s*\(|filter_var\s*\(/i', $lineText)) {continue;}
                }

                if ($this->isIgnoredLine($lineText, $code)) {continue;}
                $this->recordIssue($relative, [
                    'line' => $lineNumber,
                    'code' => $code,
                    'severity' => $rule['severity'],
                    'message' => $rule['message'],
                    'excerpt' => $this->trimExcerpt($lineText),
                ]);
            }

        }
        // Additional contextual heuristics (enhanced)
        $this->additionalHeuristics($relative, $lines, $content);
    }

    private function offsetToLine(string $content, int $offset): int
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }

    private function isIgnoredLine(string $line, string $ruleCode): bool
    {
        return str_contains($line, 'security-ignore: '.$ruleCode);
    }

    private function recordIssue(string $file, array $issue): void
    {
        $this->issues[$file][] = $issue;
        $this->totalIssues++;
    }

    private function trimExcerpt(string $line): string
    {
        $line = trim($line);
        if (strlen($line) > 180) { $line = substr($line,0,177).'...'; }
        return $line;
    }

    private function additionalHeuristics(string $relative, array $lines, string $content): void
    {
        // Heuristic: detect concatenated variable inside DB::statement or DB::select
        foreach ($lines as $idx => $line) {
            if (preg_match('/DB::(select|statement)\(.*\.(\$|\$_).*\)/', $line)) {
                if (! $this->isIgnoredLine($line, 'SQL_STRING_CONCAT')) {
                    $this->recordIssue($relative, [
                        'line' => $idx+1,
                        'code' => 'SQL_STRING_CONCAT',
                        'severity' => 'HIGH',
                        'message' => 'Potential SQL injection in DB::'.$this->extractCall($line),
                        'excerpt' => $this->trimExcerpt($line),
                    ]);
                }
            }
            // Blade unescaped
            if (str_ends_with($relative, '.blade.php') && preg_match('/{!!\s*\$[^!]+!!}/', $line)) {
                if (! $this->isIgnoredLine($line, 'BLADE_UNESCAPED')) {
                    $this->recordIssue($relative, [
                        'line' => $idx+1,
                        'code' => 'BLADE_UNESCAPED',
                        'severity' => 'HIGH',
                        'message' => 'Unescaped Blade variable output.',
                        'excerpt' => $this->trimExcerpt($line),
                    ]);
                }
            }

            // New heuristic: detect PHP variables injected into JS contexts
            if (preg_match('/<script[^>]*>/i', $line) && preg_match('/(\$\w+|\$_(GET|POST|REQUEST))/i', $line)) {
                if (! $this->isIgnoredLine($line, 'UNSAFE_JS_WITH_PHP')) {
                    $this->recordIssue($relative, [
                        'line' => $idx+1,
                        'code' => 'UNSAFE_JS_WITH_PHP',
                        'severity' => 'HIGH',
                        'message' => 'PHP variable inside <script> may lead to JS injection; use json_encode or escape.',
                        'excerpt' => $this->trimExcerpt($line),
                    ]);
                }
            }

            // JS DOM insertion heuristic: check lines that use innerHTML/insertAdjacentHTML with PHP var or superglobal
            if (preg_match('/(innerHTML|insertAdjacentHTML|document\.write|outerHTML|insertAdjacentElement)/i', $line)) {
                if (preg_match('/(\$\w+|\$_(GET|POST|REQUEST))/i', $line)) {
                    if (! $this->isIgnoredLine($line, 'JS_DOM_INJECTION')) {
                        $this->recordIssue($relative, [
                            'line' => $idx+1,
                            'code' => 'JS_DOM_INJECTION',
                            'severity' => 'HIGH',
                            'message' => 'Possible DOM injection: innerHTML/insertAdjacentHTML or document.write used with PHP variable / user input.',
                            'excerpt' => $this->trimExcerpt($line),
                        ]);
                    }
                } else {
                    // Still flag usage of document.write/innerHTML as potentially risky
                    if (preg_match('/document\.write|innerHTML|insertAdjacentHTML/i', $line)) {
                        if (! $this->isIgnoredLine($line, 'DOCUMENT_WRITE')) {
                            $this->recordIssue($relative, [
                                'line' => $idx+1,
                                'code' => 'DOCUMENT_WRITE',
                                'severity' => 'MEDIUM',
                                'message' => 'document.write/innerHTML used; check source of content.',
                                'excerpt' => $this->trimExcerpt($line),
                            ]);
                        }
                    }
                }
            }

            // Eval usage in JS or PHP strings
            if (preg_match('/\beval\s*\(/i', $line) || preg_match('/\beval\(/i', $line)) {
                if (! $this->isIgnoredLine($line, 'JS_EVAL_USAGE')) {
                    $this->recordIssue($relative, [
                        'line' => $idx+1,
                        'code' => 'JS_EVAL_USAGE',
                        'severity' => 'CRITICAL',
                        'message' => 'eval() usage detected; avoid dynamic evaluation.',
                        'excerpt' => $this->trimExcerpt($line),
                    ]);
                }
            }
        }

        // Additional file-wide heuristic: if superglobals used but no filter_input/filter_var found anywhere, emit MISSING_FILTER_INPUT once
        if (preg_match('/\$_(GET|POST|REQUEST|COOKIE)/', $content) && !preg_match('/filter_input\s*\(|filter_var\s*\(/i', $content)) {
            // locate first occurrence line
            if (preg_match('/\$_(GET|POST|REQUEST|COOKIE)/', $content, $m, PREG_OFFSET_CAPTURE)) {
                $offset = $m[0][1];
                $lineNumber = $this->offsetToLine($content, $offset);
                $lineText = $lines[$lineNumber-1] ?? '';
                if (! $this->isIgnoredLine($lineText, 'MISSING_FILTER_INPUT')) {
                    $this->recordIssue($relative, [
                        'line' => $lineNumber,
                        'code' => 'MISSING_FILTER_INPUT',
                        'severity' => 'MEDIUM',
                        'message' => 'Superglobals used without visible use of filter_input/filter_var or validation.',
                        'excerpt' => $this->trimExcerpt($lineText),
                    ]);
                }
            }
        }
    }

    private function extractCall(string $line): string
    {
        if (preg_match('/DB::(select|statement)/', $line, $m)) {return $m[1];}
        return 'raw';
    }

    private function report(): void
    {
        if ($this->json) {
            $output = [
                'checked_files' => $this->checkedFiles,
                'total_issues' => $this->totalIssues,
                'issues' => $this->issues,
                'severity_breakdown' => $this->severityBreakdown(),
                'exit_code' => $this->exitCode(),
            ];
            echo json_encode($output, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
            exit($this->exitCode());
        }

        if ($this->countsOnly) {
            $sev = $this->severityBreakdown();
            echo "FILES={$this->checkedFiles} TOTAL={$this->totalIssues} LOW={$sev['LOW']} MEDIUM={$sev['MEDIUM']} HIGH={$sev['HIGH']} CRITICAL={$sev['CRITICAL']} EXIT={$this->exitCode()}\n";
            exit($this->exitCode());
        }

        echo "\n📁 Files scanned: {$this->checkedFiles}\n";
        echo "❗ Total issues: {$this->totalIssues}\n";
        $sev = $this->severityBreakdown();
        echo "Severity: ";
        foreach ($sev as $s => $cnt) { echo "$s=$cnt "; }
        echo "\n\n";

        foreach ($this->issues as $file => $issues) {
            echo "📄 $file\n";
            echo str_repeat('-', strlen($file)+3)."\n";
            foreach ($issues as $issue) {
                echo "  Line {$issue['line']} [{$issue['severity']}] {$issue['code']} - {$issue['message']}\n";
                echo "    › {$issue['excerpt']}\n";
            }
            echo "\n";
        }
        echo "================================\n";
        echo "Legend: LOW (informational), MEDIUM (review), HIGH (likely vuln), CRITICAL (immediate action)\n";
        echo "Use // security-ignore: RULE_CODE to silence a false positive on a single line.\n";
        echo "Exit Code: {$this->exitCode()}\n";
        exit($this->exitCode());
    }

    private function severityBreakdown(): array
    {
        $counts = ['LOW'=>0,'MEDIUM'=>0,'HIGH'=>0,'CRITICAL'=>0];
        foreach ($this->issues as $fileIssues) {
            foreach ($fileIssues as $i) { $counts[$i['severity']]++; }
        }
        return $counts;
    }

    private function exitCode(): int
    {
        $sev = $this->severityBreakdown();
        // In strict mode keep legacy: any HIGH or CRITICAL => 2
        global $argv;
        $strict = in_array('--strict-exit', $argv ?? [], true);
        if ($strict) {
            if ($sev['HIGH'] > 0 || $sev['CRITICAL'] > 0) {return 2;}
            return 0;
        }
        // New logic:
        // CRITICAL > 0 => 2
        // else if HIGH > 0 => 1
        // else 0
        if ($sev['CRITICAL'] > 0) {return 2;}
        if ($sev['HIGH'] > 0) {return 1;}
        return 0;
    }
}

if (php_sapi_name() === 'cli') {
    $json = in_array('--json', $argv, true);
    $countsOnly = in_array('--counts', $argv, true);
    $pathArg = array_values(array_filter($argv, fn($a)=> str_starts_with($a,'--path=')))[0] ?? null;
    $maxArg = array_values(array_filter($argv, fn($a)=> str_starts_with($a,'--max-files=')))[0] ?? null;
    $path = $pathArg ? substr($pathArg, 7) : null;
    $maxFiles = $maxArg ? (int)substr($maxArg, 12) : 8000;
    $audit = new SecurityAudit($json, $path, $maxFiles, $countsOnly);
    $audit->run();
} else {
    echo "Run from CLI: php security_audit.php [--json] [--counts] [--path=src] [--max-files=5000]\n";
}