# Laravel Cron Setup Guide

## المشكلة
الكرون (Cron Jobs) لا يعمل بشكل صحيح لمعالجة التراخيص المنتهية والفواتير المتأخرة.

## الحل البسيط
تم إنشاء أوامر بسيطة وموثوقة لمعالجة الكرون بدون تعقيد.

## الأوامر المتاحة

### 1. اختبار الكرون
```bash
php artisan cron:test
```
- يختبر جميع وظائف الكرون
- يعرض إحصائيات التراخيص والفواتير

### 2. مراقبة حالة الكرون
```bash
php artisan cron:status
```
- يعرض حالة جميع العمليات
- يتحقق من الاتصال بقاعدة البيانات

### 3. معالجة الكرون
```bash
php artisan cron:process
```
- يعالج التراخيص المنتهية
- يعالج الفواتير المتأخرة
- ينشئ فواتير التجديد

### 4. إعداد الكرون تلقائياً
```bash
php artisan cron:setup
```
- ينشئ ملفات Windows Task Scheduler
- ينشئ ملفات Linux Cron

## إعداد Windows

### الطريقة الأولى: تلقائية
1. شغل PowerShell كمدير
2. نفذ: `.\setup-windows-cron.ps1`

### الطريقة الثانية: يدوية
1. افتح Windows Task Scheduler
2. أنشئ مهمة جديدة
3. اربطها بملف `cron-runner.bat`
4. اضبط التوقيت على يومياً الساعة 8:00 صباحاً

## إعداد Linux

### الطريقة الأولى: تلقائية
```bash
php artisan cron:setup --platform=linux
crontab crontab.txt
```

### الطريقة الثانية: يدوية
```bash
crontab -e
# أضف السطور التالية:
0 8 * * * cd /path/to/your/project && php artisan licenses:generate-renewal-invoices --days=7
0 9 * * * cd /path/to/your/project && php artisan invoices:process
0 * * * * cd /path/to/your/project && php artisan invoices:process --overdue
```

## الاختبار

### اختبار يدوي
```bash
# اختبار جميع الوظائف
php artisan cron:test

# معالجة يدوية
php artisan cron:process

# فحص الحالة
php artisan cron:status
```

### اختبار Windows
```bash
# شغل الملف مباشرة
.\cron-runner.bat
```

## المميزات

✅ **بسيط وسهل الصيانة**
- أوامر واضحة ومفهومة
- لا يوجد تعقيد في الكود
- سهولة التشخيص

✅ **موثوق**
- معالجة الأخطاء الشاملة
- تسجيل مفصل للأخطاء
- معاملات قاعدة البيانات آمنة

✅ **مرن**
- يعمل على Windows و Linux
- إعداد تلقائي ويدوي
- اختبار سهل

## استكشاف الأخطاء

### إذا لم يعمل الكرون:
1. تحقق من الأذونات
2. تأكد من مسار PHP
3. اختبر الأوامر يدوياً
4. راجع ملفات السجل

### أوامر التشخيص:
```bash
# فحص الحالة
php artisan cron:status

# اختبار الوظائف
php artisan cron:test

# معالجة يدوية
php artisan cron:process
```

## ملاحظات مهمة

- الكرون يعمل يومياً الساعة 8:00 صباحاً
- معالجة الفواتير المتأخرة كل ساعة
- جميع العمليات مسجلة في ملفات السجل
- يمكن تشغيل الأوامر يدوياً في أي وقت
