<?php
/**
 * Script to fix documentation errors in PHP files
 */

function fixDocumentation($directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    $fixedFiles = 0;
    $totalFiles = 0;
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $totalFiles++;
            $filePath = $file->getPathname();
            
            // Skip vendor directory
            if (strpos($filePath, 'vendor/') !== false) {
                continue;
            }
            
            $content = file_get_contents($filePath);
            $originalContent = $content;
            
            // Fix @return tags that are grouped with @param tags
            // Pattern: @param ... @return should be @param ... \n * @return
            $content = preg_replace(
                '/(\s*\*\s*@param[^\n]*\n)(\s*\*\s*@return)/m',
                "$1     *\n$2",
                $content
            );
            
            // Fix multiple @param tags followed by @return
            $content = preg_replace(
                '/(\s*\*\s*@param[^\n]*\n)(\s*\*\s*@param[^\n]*\n)(\s*\*\s*@return)/m',
                "$1$2     *\n$3",
                $content
            );
            
            // Fix file-level docblock issues in routes
            if (strpos($filePath, 'routes/') !== false) {
                // Add proper file header for routes
                if (strpos($content, '/**') === false && strpos($content, '<?php') === 0) {
                    $content = str_replace(
                        '<?php',
                        "<?php\n\n/**\n * Web Routes\n *\n * Here is where you can register web routes for your application.\n */",
                        $content
                    );
                }
            }
            
            // Write back to file if content changed
            if ($content !== $originalContent) {
                file_put_contents($filePath, $content);
                $fixedFiles++;
                echo "Fixed documentation in: $filePath\n";
            }
        }
    }
    
    echo "\nTotal files processed: $totalFiles\n";
    echo "Files fixed: $fixedFiles\n";
}

// Run the fix
echo "Starting documentation fix...\n";
fixDocumentation('app/');
fixDocumentation('routes/');
echo "Done!\n";
