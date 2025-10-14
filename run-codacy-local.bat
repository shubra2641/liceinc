@echo off
echo Running Codacy Analysis Locally...

REM Install Codacy CLI if not already installed
npm install -g codacy-analysis-cli

REM Run Codacy analysis
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --output-format json --output-file codacy-local-results.json

echo.
echo Codacy analysis completed!
echo Results saved to: codacy-local-results.json

pause
