@echo off
echo Running Complete Codacy Analysis...

REM Install dependencies
composer install --prefer-dist --no-progress --no-suggest

REM Run all tests
echo Running PHPUnit tests...
vendor\bin\phpunit --configuration=phpunit.xml --coverage-html=storage/app/coverage/html --coverage-clover=storage/app/coverage/clover.xml --coverage-text=storage/app/coverage/coverage.txt

REM Run PHPStan
echo Running PHPStan analysis...
vendor\bin\phpstan analyse --memory-limit=1G --error-format=github --no-progress --level=8

REM Run PHP CodeSniffer
echo Running PHP CodeSniffer...
vendor\bin\phpcs --standard=PSR12 --report=checkstyle --report-file=phpcs-report.xml app/

REM Run Laravel Pint
echo Running Laravel Pint...
vendor\bin\pint --test

REM Run security check
echo Running security check...
vendor\bin\security-checker security:check composer.lock

REM Run Trivy
echo Running Trivy security scan...
trivy fs . --format json --output trivy-results.json

REM Run Codacy complete analysis
echo Running Codacy complete analysis...
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpcs,phpstan,trivy,eslint --output-format json --output-file codacy-complete-results.json

echo.
echo Complete analysis finished!
echo Results saved to:
echo - codacy-complete-results.json
echo - phpcs-report.xml
echo - trivy-results.json
echo - storage/app/coverage/html/index.html

pause
