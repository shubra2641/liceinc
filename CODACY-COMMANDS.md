# Codacy Analysis Commands

## 🚀 أوامر تشغيل تحليل Codacy

### 1. **تشغيل محلي أساسي**:
```bash
# تثبيت Codacy CLI
npm install -g codacy-analysis-cli

# تشغيل التحليل الأساسي
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --output-format json --output-file codacy-results.json
```

### 2. **تشغيل مع أدوات PHP**:
```bash
# تثبيت التبعيات
composer install --prefer-dist --no-progress --no-suggest

# تشغيل PHPStan
vendor\bin\phpstan analyse --memory-limit=1G --error-format=github --no-progress --level=8

# تشغيل PHP CodeSniffer
vendor\bin\phpcs --standard=PSR12 --report=checkstyle --report-file=phpcs-report.xml app/

# تشغيل Laravel Pint
vendor\bin\pint --test

# تشغيل Codacy مع أدوات PHP
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpcs,phpstan,trivy --output-format json --output-file codacy-php-results.json
```

### 3. **تشغيل تحليل الأمان**:
```bash
# فحص أمان Composer
vendor\bin\security-checker security:check composer.lock

# فحص Trivy
trivy fs . --format json --output trivy-results.json

# تشغيل Codacy للأمان
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools trivy --output-format json --output-file codacy-security-results.json
```

### 4. **تشغيل تحليل الأداء**:
```bash
# تشغيل PHPUnit مع التغطية
vendor\bin\phpunit --configuration=phpunit.xml --coverage-html=storage/app/coverage/html --coverage-clover=storage/app/coverage/clover.xml --coverage-text=storage/app/coverage/coverage.txt

# تشغيل Codacy للأداء
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpstan,trivy --output-format json --output-file codacy-performance-results.json
```

### 5. **تشغيل تحليل شامل**:
```bash
# تشغيل جميع الاختبارات
vendor\bin\phpunit --configuration=phpunit.xml --coverage-html=storage/app/coverage/html --coverage-clover=storage/app/coverage/clover.xml --coverage-text=storage/app/coverage/coverage.txt

# تشغيل PHPStan
vendor\bin\phpstan analyse --memory-limit=1G --error-format=github --no-progress --level=8

# تشغيل PHP CodeSniffer
vendor\bin\phpcs --standard=PSR12 --report=checkstyle --report-file=phpcs-report.xml app/

# تشغيل Laravel Pint
vendor\bin\pint --test

# فحص الأمان
vendor\bin\security-checker security:check composer.lock

# فحص Trivy
trivy fs . --format json --output trivy-results.json

# تشغيل Codacy شامل
codacy-analysis-cli analyze --token IJ2F1RZG6BfH3B7FTRdl --directory . --tools phpcs,phpstan,trivy,eslint --output-format json --output-file codacy-complete-results.json
```

## 📊 ملفات النتائج

### تقارير Codacy:
- `codacy-results.json` - نتائج التحليل الأساسي
- `codacy-php-results.json` - نتائج تحليل PHP
- `codacy-security-results.json` - نتائج تحليل الأمان
- `codacy-performance-results.json` - نتائج تحليل الأداء
- `codacy-complete-results.json` - نتائج التحليل الشامل

### تقارير PHP:
- `phpcs-report.xml` - تقرير PHP CodeSniffer
- `phpstan-results.json` - تقرير PHPStan

### تقارير الأمان:
- `trivy-results.json` - تقرير Trivy
- `security-check-results.txt` - تقرير Composer Security Checker

### تقارير التغطية:
- `storage/app/coverage/html/index.html` - تقرير HTML تفاعلي
- `storage/app/coverage/clover.xml` - تقرير Clover XML
- `storage/app/coverage/coverage.txt` - تقرير نصي

## 🔧 خيارات Codacy CLI

### الأدوات المتاحة:
- `phpcs` - PHP CodeSniffer
- `phpstan` - PHPStan static analysis
- `trivy` - Security vulnerability scanner
- `eslint` - JavaScript code quality
- `pylint` - Python code quality

### تنسيقات الإخراج:
- `json` - JSON format
- `xml` - XML format
- `text` - Plain text format

### خيارات إضافية:
- `--directory .` - تحليل المجلد الحالي
- `--output-file filename.json` - حفظ النتائج في ملف
- `--tools tool1,tool2` - تحديد الأدوات
- `--max-issues 1000` - الحد الأقصى للمشاكل
- `--fail-build-on-issues` - إيقاف البناء عند وجود مشاكل

## 🚀 تشغيل سريع

### Windows:
```cmd
run-codacy-complete.bat
```

### Linux/Mac:
```bash
chmod +x run-codacy.sh
./run-codacy.sh
```

### PowerShell:
```powershell
.\run-codacy.ps1
```

## 📈 مراقبة النتائج

### فحص النتائج:
```bash
# عرض نتائج Codacy
cat codacy-results.json

# عرض تقرير التغطية
cat storage/app/coverage/coverage.txt

# فتح تقرير HTML
start storage/app/coverage/html/index.html
```

### تحليل النتائج:
```bash
# عد المشاكل
jq '.summary.totalIssues' codacy-results.json

# عرض المشاكل الجديدة
jq '.summary.newIssues' codacy-results.json

# عرض المشاكل المُصلحة
jq '.summary.fixedIssues' codacy-results.json
```
