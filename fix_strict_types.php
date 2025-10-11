<?php
/**
 * Script to add declare(strict_types=1); to all PHP files that don't have it
 */

function fixStrictTypes($directory) {
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
            
            // Check if file already has declare(strict_types=1);
            if (strpos($content, 'declare(strict_types=1);') !== false) {
                continue;
            }
            
            // Check if file starts with <?php
            if (strpos($content, '<?php') === 0) {
                // Add declare(strict_types=1); after <?php
                $newContent = str_replace(
                    '<?php',
                    "<?php\n\ndeclare(strict_types=1);",
                    $content
                );
                
                // Write back to file
                file_put_contents($filePath, $newContent);
                $fixedFiles++;
                echo "Fixed: $filePath\n";
            }
        }
    }
    
    echo "\nTotal files processed: $totalFiles\n";
    echo "Files fixed: $fixedFiles\n";
}

// Run the fix
echo "Starting strict_types fix...\n";
fixStrictTypes('app/');
fixStrictTypes('config/');
fixStrictTypes('database/');
fixStrictTypes('routes/');
echo "Done!\n";
