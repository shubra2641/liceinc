# إعداد cPanel Cron Jobs

## الأوامر المطلوبة في cPanel:

### 1. معالجة يومية (الساعة 9:00 صباحاً)
```
0 9 * * * cd /home/yourusername/public_html && php artisan cron:process
```

### 2. معالجة الفواتير كل ساعة
```
0 * * * * cd /home/yourusername/public_html && php artisan cron:process --type=invoices
```

### 3. إنشاء فواتير التجديد (الساعة 8:00 صباحاً)
```
0 8 * * * cd /home/yourusername/public_html && php artisan licenses:generate-renewal-invoices --days=7
```

## كيفية الإعداد:

1. ادخل إلى cPanel
2. اذهب إلى "Cron Jobs"
3. أضف الأوامر أعلاه
4. تأكد من تغيير `yourusername` إلى اسم المستخدم الخاص بك
5. تأكد من أن مسار المشروع صحيح

## اختبار الأوامر:

```bash
# اختبار شامل
php artisan cron:test

# معالجة يدوية
php artisan cron:process

# فحص الحالة
php artisan cron:status
```

## ملاحظات مهمة:

- تأكد من أن PHP يعمل في المسار الصحيح
- تحقق من صلاحيات الملفات
- راجع ملفات السجل في حالة وجود أخطاء
