# PowerShell script for Codacy analysis

Write-Host "Running Codacy Analysis with PowerShell..." -ForegroundColor Green

# Install Codacy CLI if not already installed
Write-Host "Installing Codacy CLI..." -ForegroundColor Yellow
npm install -g codacy-analysis-cli

# Install PHP dependencies
Write-Host "Installing PHP dependencies..." -ForegroundColor Yellow
composer install --prefer-dist --no-progress --no-suggest

# Run PHPUnit tests
Write-Host "Running PHPUnit tests..." -ForegroundColor Yellow
vendor\bin\phpunit --configuration=phpunit.xml --coverage-html=storage/app/coverage/html --coverage-clover=storage/app/coverage/clover.xml --coverage-text=storage/app/coverage/coverage.txt

# Run PHPStan
Write-Host "Running PHPStan analysis..." -ForegroundColor Yellow
vendor\bin\phpstan analyse --memory-limit=1G --error-format=github --no-progress --level=8

# Run PHP CodeSniffer
Write-Host "Running PHP CodeSniffer..." -ForegroundColor Yellow
vendor\bin\phpcs --standard=PSR12 --report=checkstyle --report-file=phpcs-report.xml app/

# Run Laravel Pint
Write-Host "Running Laravel Pint..." -ForegroundColor Yellow
vendor\bin\pint --test

# Run security check
Write-Host "Running security check..." -ForegroundColor Yellow
vendor\bin\security-checker security:check composer.lock

# Run Trivy
Write-Host "Running Trivy security scan..." -ForegroundColor Yellow
trivy fs . --format json --output trivy-results.json

# Run Codacy analysis
Write-Host "Running Codacy analysis..." -ForegroundColor Yellow
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpcs,phpstan,trivy,eslint --output-format json --output-file codacy-results.json

Write-Host "Analysis completed!" -ForegroundColor Green
Write-Host "Results saved to:" -ForegroundColor Cyan
Write-Host "- codacy-results.json" -ForegroundColor White
Write-Host "- phpcs-report.xml" -ForegroundColor White
Write-Host "- trivy-results.json" -ForegroundColor White
Write-Host "- storage/app/coverage/html/index.html" -ForegroundColor White
