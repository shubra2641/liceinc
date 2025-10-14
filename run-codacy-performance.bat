@echo off
echo Running Codacy Performance Analysis...

REM Install dependencies
composer install --prefer-dist --no-progress --no-suggest

REM Run PHPUnit with coverage
echo Running PHPUnit with coverage...
vendor\bin\phpunit --configuration=phpunit.xml --coverage-html=storage/app/coverage/html --coverage-clover=storage/app/coverage/clover.xml --coverage-text=storage/app/coverage/coverage.txt

REM Run performance analysis
echo Analyzing performance patterns...
echo Checking for N+1 queries...
findstr /r /s "->get()" app\*.php

echo Checking for nested loops...
findstr /r /s "foreach.*foreach\|for.*for" app\*.php

echo Checking for caching opportunities...
findstr /r /s "Cache::\|cache()" app\*.php

REM Run Codacy performance analysis
echo Running Codacy performance analysis...
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpstan,trivy --output-format json --output-file codacy-performance-results.json

echo.
echo Performance analysis completed!
echo Results saved to:
echo - codacy-performance-results.json
echo - storage/app/coverage/html/index.html

pause
