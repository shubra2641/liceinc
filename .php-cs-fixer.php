<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/routes',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
$config->setRules([
    // PHP 8+ compatibility
    '@PHP80Migration' => true,
    '@PHP81Migration' => true,
    
    // PSR standards
    '@PSR12' => true,
    
    // Array formatting
    'array_syntax' => ['syntax' => 'short'],
    'array_indentation' => true,
    'trim_array_spaces' => true,
    
    // Class and method formatting
    'class_attributes_separation' => [
        'elements' => [
            'method' => 'one',
            'property' => 'one',
        ],
    ],
    
    // Control structures
    'elseif' => true,
    'no_superfluous_elseif' => true,
    'no_unneeded_control_parentheses' => true,
    'yoda_style' => false,
    
    // Import statements
    'fully_qualified_strict_types' => true,
    'no_unused_imports' => true,
    'ordered_imports' => [
        'imports_order' => ['class', 'function', 'const'],
        'sort_algorithm' => 'alpha',
    ],
    
    // Type declarations
    'declare_strict_types' => true,
    'no_superfluous_phpdoc_tags' => [
        'allow_mixed' => true,
        'allow_unused_params' => false,
        'remove_inheritdoc' => true,
    ],
    
    // PHPDoc
    'phpdoc_align' => ['align' => 'left'],
    'phpdoc_indent' => true,
    'phpdoc_no_useless_inheritdoc' => true,
    'phpdoc_order' => true,
    'phpdoc_scalar' => true,
    'phpdoc_separation' => true,
    'phpdoc_summary' => true,
    'phpdoc_trim' => true,
    'phpdoc_types' => true,
    
    // Variable formatting
    'no_unused_imports' => true,
    'single_quote' => true,
    
    // Whitespace
    'compact_nullable_typehint' => true,
    'concat_space' => ['spacing' => 'one'],
    'no_empty_statement' => true,
    'no_extra_blank_lines' => true,
    'no_trailing_whitespace' => true,
    'no_whitespace_in_blank_line' => true,
    'trailing_comma_in_multiline' => [
        'elements' => ['arrays', 'arguments', 'parameters'],
    ],
]);

$config->setFinder($finder);
$config->setRiskyAllowed(true);
$config->setUsingCache(true);
$config->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');

return $config;