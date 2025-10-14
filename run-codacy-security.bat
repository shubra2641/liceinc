@echo off
echo Running Codacy Security Analysis...

REM Install dependencies
composer install --prefer-dist --no-progress --no-suggest

REM Run security checks
echo Running Composer security check...
vendor\bin\security-checker security:check composer.lock

REM Run Trivy security scan
echo Running Trivy security scan...
trivy fs . --format json --output trivy-results.json

REM Run Codacy security analysis
echo Running Codacy security analysis...
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools trivy --output-format json --output-file codacy-security-results.json

echo.
echo Security analysis completed!
echo Results saved to:
echo - codacy-security-results.json
echo - trivy-results.json

pause
