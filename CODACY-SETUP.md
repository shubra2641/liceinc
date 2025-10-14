# Codacy Integration Setup

## Overview
This project is configured to work with Codacy for automated code quality analysis. Codacy provides comprehensive code quality checks, security analysis, and coverage reporting.

## Setup Steps

### 1. Codacy Account Setup
1. Go to [Codacy.com](https://www.codacy.com)
2. Sign up or log in with your GitHub account
3. Connect your GitHub repository
4. Get your API tokens from [Account Settings](https://app.codacy.com/account/api)

### 2. API Tokens Required
- **Account API Token**: `IJ2F1RZG6BfH3B7FTRdl`
- **Repository API Token**: `d548a8b2566044a7b8ad30f1fc43febe`

### 3. GitHub Secrets Configuration
Add the following secrets to your GitHub repository:

1. Go to your repository on GitHub
2. Navigate to Settings → Secrets and variables → Actions
3. Add the following secrets:
   - `CODACY_PROJECT_TOKEN`: `d548a8b2566044a7b8ad30f1fc43febe`
   - `CODACY_ACCOUNT_TOKEN`: `IJ2F1RZG6BfH3B7FTRdl`

### 4. Workflow Files
The following GitHub Actions workflows are configured:

#### `codacy-analysis.yml`
- Runs Codacy analysis on every push and PR
- Generates quality reports
- Comments on PRs with analysis results

#### `comprehensive-analysis.yml`
- Combines Codacy with other quality tools
- Includes PHPStan, PHPCS, security scans
- Generates comprehensive reports

#### `php-quality-analysis.yml`
- PHP-specific quality analysis
- PHPStan, PHPCS, Laravel Pint
- Style and static analysis

#### `security-analysis.yml`
- Security vulnerability scanning
- Trivy, Composer security check
- XSS and SQL injection detection

#### `performance-analysis.yml`
- Performance analysis
- Database query optimization
- Memory usage analysis

### 5. Configuration Files

#### `.codacy.yml`
Main Codacy configuration file with:
- Excluded paths (vendor, node_modules, etc.)
- Included paths (app, config, routes)
- Tool configuration (phpcs, phpstan, trivy)
- Quality gates
- Coverage thresholds

#### `phpunit.xml`
PHPUnit configuration with coverage settings:
- HTML coverage reports
- Clover XML reports
- Text coverage reports
- Excluded directories

### 6. Running Analysis

#### Automatic (GitHub Actions)
- Analysis runs automatically on push/PR
- Results are posted as comments
- Reports are uploaded as artifacts

#### Manual (Local)
```bash
# Install Codacy CLI
npm install -g codacy-analysis-cli

# Run analysis
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl
```

### 7. Quality Gates

#### Coverage Thresholds
- Line Coverage: 80%
- Branch Coverage: 70%
- Function Coverage: 80%
- Class Coverage: 80%

#### Quality Checks
- Security vulnerabilities
- Code style violations
- Static analysis issues
- Performance problems

### 8. Reports and Artifacts

#### Generated Reports
- HTML coverage reports
- Clover XML reports
- Text coverage summaries
- Security scan results
- Quality analysis results

#### Artifact Locations
- `storage/app/coverage/` - Coverage reports
- `phpcs-report.xml` - Code style report
- `trivy-results.sarif` - Security scan
- `codacy-analysis-results.json` - Codacy results

### 9. Troubleshooting

#### Common Issues
1. **API Token Invalid**: Check token permissions
2. **Analysis Fails**: Check file permissions
3. **Coverage Low**: Add more tests
4. **Security Issues**: Review flagged code

#### Debug Steps
1. Check GitHub Actions logs
2. Verify API tokens
3. Review Codacy dashboard
4. Check file exclusions

### 10. Best Practices

#### Code Quality
- Follow PSR-12 coding standards
- Use type hints and return types
- Write comprehensive tests
- Document complex logic

#### Security
- Sanitize user input
- Use prepared statements
- Validate all data
- Keep dependencies updated

#### Performance
- Use eager loading
- Implement caching
- Optimize database queries
- Monitor memory usage

### 11. Integration Benefits

#### Automated Quality Checks
- Continuous code quality monitoring
- Automated security scanning
- Performance analysis
- Coverage tracking

#### Team Collaboration
- PR quality gates
- Automated reviews
- Quality metrics
- Progress tracking

#### CI/CD Integration
- Quality gates in pipeline
- Automated reporting
- Artifact generation
- Status checks

### 12. Monitoring and Alerts

#### Quality Metrics
- Code coverage percentage
- Security vulnerability count
- Code style violations
- Performance metrics

#### Alerts
- Quality gate failures
- Security vulnerabilities
- Coverage drops
- Performance regressions

### 13. Customization

#### Tool Configuration
- Enable/disable specific tools
- Configure tool options
- Set quality thresholds
- Customize reports

#### Workflow Customization
- Modify trigger conditions
- Add custom steps
- Configure notifications
- Set up integrations

### 14. Support

#### Documentation
- [Codacy Documentation](https://docs.codacy.com)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [PHPUnit Documentation](https://phpunit.readthedocs.io)

#### Community
- [Codacy Community](https://community.codacy.com)
- [GitHub Discussions](https://github.com/features/discussions)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/codacy)

### 15. Maintenance

#### Regular Tasks
- Update API tokens
- Review quality reports
- Update tool configurations
- Clean old artifacts

#### Monitoring
- Check analysis results
- Review quality trends
- Monitor coverage changes
- Track security issues
