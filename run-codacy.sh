#!/bin/bash

echo "Running Codacy Analysis..."

# Install Codacy CLI if not already installed
echo "Installing Codacy CLI..."
npm install -g codacy-analysis-cli

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --prefer-dist --no-progress --no-suggest

# Run PHPUnit tests
echo "Running PHPUnit tests..."
vendor/bin/phpunit --configuration=phpunit.xml --coverage-html=storage/app/coverage/html --coverage-clover=storage/app/coverage/clover.xml --coverage-text=storage/app/coverage/coverage.txt

# Run PHPStan
echo "Running PHPStan analysis..."
vendor/bin/phpstan analyse --memory-limit=1G --error-format=github --no-progress --level=8

# Run PHP CodeSniffer
echo "Running PHP CodeSniffer..."
vendor/bin/phpcs --standard=PSR12 --report=checkstyle --report-file=phpcs-report.xml app/

# Run Laravel Pint
echo "Running Laravel Pint..."
vendor/bin/pint --test

# Run security check
echo "Running security check..."
vendor/bin/security-checker security:check composer.lock

# Run Trivy
echo "Running Trivy security scan..."
trivy fs . --format json --output trivy-results.json

# Run Codacy analysis
echo "Running Codacy analysis..."
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpcs,phpstan,trivy,eslint --output-format json --output-file codacy-results.json

echo "Analysis completed!"
echo "Results saved to:"
echo "- codacy-results.json"
echo "- phpcs-report.xml"
echo "- trivy-results.json"
echo "- storage/app/coverage/html/index.html"
