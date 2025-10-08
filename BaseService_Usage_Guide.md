# دليل استخدام Base Service في Laravel

## نظرة عامة

`BaseService` هو فئة أساسية توفر وظائف مشتركة لجميع Services في المشروع، مما يضمن:
- **إدارة المعاملات الآمنة**
- **تسجيل الأخطاء الموحد**
- **التحقق من المدخلات**
- **تنظيف البيانات**
- **أمان شامل**

## كيفية استخدام Base Service

### 1. إنشاء Service جديد

```php
<?php

namespace App\Services;

use App\Models\User;

class NewService extends BaseService
{
    public function createUser(array $data): User
    {
        // استخدام طرق التحقق من BaseService
        $this->validateNotEmpty($data['name'] ?? null, 'Name');
        $this->validateEmail($data['email'] ?? null, 'Email');
        
        // استخدام إدارة المعاملات
        return $this->executeInTransaction(function () use ($data) {
            $user = User::create([
                'name' => $this->sanitizeInput($data['name']),
                'email' => $this->sanitizeInput($data['email']),
            ]);
            
            // تسجيل النجاح
            $this->logInfo('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return $user;
        });
    }
}
```

### 2. استخدام إدارة المعاملات

```php
public function transferMoney($fromAccount, $toAccount, $amount): array
{
    return $this->executeInTransaction(function () use ($fromAccount, $toAccount, $amount) {
        // خصم من الحساب الأول
        $fromAccount->decrement('balance', $amount);
        
        // إضافة للحساب الثاني
        $toAccount->increment('balance', $amount);
        
        // تسجيل العملية
        $this->logInfo('Money transfer completed', [
            'from' => $fromAccount->id,
            'to' => $toAccount->id,
            'amount' => $amount
        ]);
        
        return ['success' => true, 'amount' => $amount];
    });
}
```

### 3. استخدام تسجيل الأخطاء

```php
public function processData(array $data): array
{
    try {
        // معالجة البيانات
        $result = $this->processDataLogic($data);
        
        // تسجيل النجاح
        $this->logInfo('Data processed successfully', [
            'data_count' => count($data),
            'result_count' => count($result)
        ]);
        
        return $result;
    } catch (\Exception $e) {
        // تسجيل الخطأ (يتم تلقائياً في BaseService)
        $this->logError('Data processing failed', [
            'error' => $e->getMessage(),
            'data' => $data
        ]);
        
        throw $e;
    }
}
```

### 4. استخدام التحقق من المدخلات

```php
public function validateUserInput(array $input): void
{
    // التحقق من عدم الفراغ
    $this->validateNotEmpty($input['name'] ?? null, 'Name');
    $this->validateNotEmpty($input['email'] ?? null, 'Email');
    
    // التحقق من البريد الإلكتروني
    $this->validateEmail($input['email'], 'Email');
    
    // التحقق من الأرقام الموجبة
    if (isset($input['age'])) {
        $this->validatePositiveInteger($input['age'], 'Age');
    }
    
    // التحقق من الأحرف الأبجدية الرقمية
    if (isset($input['code'])) {
        $this->validateAlphanumeric($input['code'], 'Code');
    }
}
```

### 5. استخدام تنظيف البيانات

```php
public function saveUserData(array $rawData): User
{
    return $this->executeInTransaction(function () use ($rawData) {
        $user = User::create([
            'name' => $this->sanitizeInput($rawData['name']),
            'email' => $this->sanitizeInput($rawData['email']),
            'description' => $this->sanitizeInput($rawData['description'] ?? ''),
        ]);
        
        return $user;
    });
}
```

## الطرق المتاحة في BaseService

### إدارة المعاملات
```php
$this->executeInTransaction(function () {
    // عمليات قاعدة البيانات
    return $result;
});
```

### تسجيل الأخطاء والمعلومات
```php
$this->logError('Error message', ['context' => 'data']);
$this->logInfo('Info message', ['context' => 'data']);
```

### التحقق من المدخلات
```php
$this->validateNotEmpty($value, 'Field Name');
$this->validateEmail($email, 'Email');
$this->validatePositiveInteger($number, 'Number');
$this->validateAlphanumeric($code, 'Code');
```

### تنظيف البيانات
```php
$cleanData = $this->sanitizeInput($rawData);
```

### توليد الرموز الآمنة
```php
$token = $this->generateSecureToken(32); // 32 حرف
```

## أمثلة عملية

### مثال 1: إنشاء مستخدم مع التحقق
```php
public function createUser(array $userData): User
{
    // التحقق من البيانات
    $this->validateNotEmpty($userData['name'] ?? null, 'Name');
    $this->validateEmail($userData['email'] ?? null, 'Email');
    
    return $this->executeInTransaction(function () use ($userData) {
        $user = User::create([
            'name' => $this->sanitizeInput($userData['name']),
            'email' => $this->sanitizeInput($userData['email']),
            'password' => bcrypt($this->generateSecureToken(16)),
        ]);
        
        $this->logInfo('User created', ['user_id' => $user->id]);
        return $user;
    });
}
```

### مثال 2: معالجة الدفع
```php
public function processPayment(array $paymentData): array
{
    $this->validateNotEmpty($paymentData['amount'] ?? null, 'Amount');
    $this->validatePositiveInteger($paymentData['amount'], 'Amount');
    
    return $this->executeInTransaction(function () use ($paymentData) {
        // معالجة الدفع
        $payment = Payment::create([
            'amount' => $paymentData['amount'],
            'currency' => $this->sanitizeInput($paymentData['currency'] ?? 'USD'),
            'status' => 'pending',
        ]);
        
        $this->logInfo('Payment processed', [
            'payment_id' => $payment->id,
            'amount' => $payment->amount
        ]);
        
        return ['success' => true, 'payment_id' => $payment->id];
    });
}
```

### مثال 3: معالجة الملفات
```php
public function uploadFile(UploadedFile $file): array
{
    $this->validateNotEmpty($file, 'File');
    
    return $this->executeInTransaction(function () use ($file) {
        $filename = $this->generateSecureToken(16) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename);
        
        $fileRecord = File::create([
            'original_name' => $this->sanitizeInput($file->getClientOriginalName()),
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
        ]);
        
        $this->logInfo('File uploaded', [
            'file_id' => $fileRecord->id,
            'filename' => $filename
        ]);
        
        return ['success' => true, 'file_id' => $fileRecord->id];
    });
}
```

## أفضل الممارسات

### 1. دائماً استخدم BaseService للـ Services الجديدة
```php
class NewService extends BaseService
{
    // Service implementation
}
```

### 2. استخدم executeInTransaction للعمليات الحساسة
```php
return $this->executeInTransaction(function () {
    // عمليات قاعدة البيانات
});
```

### 3. استخدم طرق التحقق قبل المعالجة
```php
$this->validateNotEmpty($data['field'], 'Field Name');
$this->validateEmail($data['email'], 'Email');
```

### 4. استخدم sanitizeInput لتنظيف البيانات
```php
$cleanData = $this->sanitizeInput($rawData);
```

### 5. استخدم logging للعمليات المهمة
```php
$this->logInfo('Operation completed', ['context' => 'data']);
$this->logError('Operation failed', ['error' => $e->getMessage()]);
```

## التحويل من Services الموجودة

### قبل التحويل:
```php
class OldService
{
    public function createUser($data)
    {
        DB::beginTransaction();
        try {
            $user = User::create($data);
            DB::commit();
            Log::info('User created');
            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
```

### بعد التحويل:
```php
class NewService extends BaseService
{
    public function createUser(array $data): User
    {
        $this->validateNotEmpty($data['name'] ?? null, 'Name');
        
        return $this->executeInTransaction(function () use ($data) {
            $user = User::create([
                'name' => $this->sanitizeInput($data['name']),
                'email' => $this->sanitizeInput($data['email']),
            ]);
            
            $this->logInfo('User created', ['user_id' => $user->id]);
            return $user;
        });
    }
}
```

## الخلاصة

استخدام `BaseService` يوفر:
- ✅ **أمان أكبر** مع التحقق من المدخلات
- ✅ **كود أنظف** مع إزالة التكرار
- ✅ **إدارة أفضل للمعاملات** مع rollback تلقائي
- ✅ **تسجيل موحد** للأخطاء والمعلومات
- ✅ **صيانة أسهل** مع كود منظم

**استخدم BaseService في جميع Services الجديدة لضمان جودة عالية وأمان شامل! 🚀**
