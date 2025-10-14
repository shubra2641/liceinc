@echo off
echo Running Codacy Analysis with PHP Tools...

REM Install dependencies
composer install --prefer-dist --no-progress --no-suggest

REM Run PHPStan
echo Running PHPStan analysis...
vendor\bin\phpstan analyse --memory-limit=1G --error-format=github --no-progress --level=8

REM Run PHP CodeSniffer
echo Running PHP CodeSniffer...
vendor\bin\phpcs --standard=PSR12 --report=checkstyle --report-file=phpcs-report.xml app/

REM Run Laravel Pint
echo Running Laravel Pint...
vendor\bin\pint --test

REM Run Codacy analysis
echo Running Codacy analysis...
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpcs,phpstan,trivy --output-format json --output-file codacy-php-results.json

echo.
echo All analyses completed!
echo Results saved to:
echo - codacy-php-results.json
echo - phpcs-report.xml

pause
