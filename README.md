# 🚀 MGWA WhatsApp Gateway Client (PHP / Laravel SDK)

مكتبة برمجة متكاملة باللغة العربية للربط والاستخدام المباشر مع **بوابة واتساب MGWA (WhatsApp Gateway)**. تتيح لك إرسال واستقبال رسائل الواتساب، إدارة الجلسات، جلب رموز الـ QR، ومعالجة أحداث الـ Webhooks بسهولة تامة وبكفاءة عالية في تطبيقات **PHP النظيفة** أو أطر عمل **Laravel**.

---

## 📋 المميزات الرئيسيّة (Features)

* ⚡ **دعم كامل لجميع نقاط API بوابة MGWA (API v1)**.
* 📨 **إرسال الرسائل النصية المباشرة والمجمعة**.
* 🖼️ **إرسال الوسائط والملفات (صور، فيديوهات، مستندات PDF)** مع نص مصلح (Caption).
* 🔄 **دعم نمط الإرسال المباشر أو الفوري والجدولة الخلفية (Async / Queued)**.
* 📲 **إدارة واستعلام حالة الجلسات (Sessions Status & QR Code Retrieval)**.
* 🔔 **معالج Webhook ذكي ومبسط (Webhook Handler)** مع دعم التحقق من التوقيع السري `X-Webhook-Secret`.
* 🛡️ **نظام استثناءات وأخطاء واضح ودقيق (`MgwaException`)**.
* 📦 **تكامل سلس مع Laravel (ServiceProvider & Facade)** لتسهيل استدعاء `Mgwa::sendMessage()`.
* 💬 **جميع التعليقات البرمجية والتوثيقات مكتوبة باللغة العربية بالكامل**.

---

## 🛠️ التثبيت (Installation)

### 1. التثبيت عبر Composer

إذا كانت المكتبة منشورة كحزمة Composer أو مرتبطة محلياً، يمكنك تثبيتها بتشغيل:

```bash
composer require mgwa/whatsapp-client
```

إذا كنت تستخدم الحزمة كـ Local Package داخل مشروع Laravel خاص بك، أضف السطر التالي في ملف `composer.json` الرئيسي لمشروعك تحت قسم `autoload.psr-4`:

```json
"autoload": {
    "psr-4": {
        "Mgwa\\Client\\": "packages/mgwa-client/src/"
    }
}
```

ثم قم بتحديث الـ Autoloader:

```bash
composer dump-autoload
```

---

## ⚙️ التكوين والتهيئة (Configuration)

### أولاً: في بيئة Laravel

المكتبة تدعم التكتشف التلقائي (Auto-Discovery). لنشر ملف التكوين إلى مشروعك تشغيل الأمر التالي:

```bash
php artisan vendor:publish --tag=mgwa-config
```

سينتج ملف التكوين `config/mgwa.php`. قم بإضافة المتغيرات التالية في ملف البيئة `.env`:

```env
MGWA_BASE_URL=https://mgwa.yourdomain.com
MGWA_API_TOKEN=your_sanctum_api_token_here
MGWA_DEFAULT_SESSION=session_identifier_or_id
MGWA_WEBHOOK_SECRET=your_optional_webhook_secret
```

---

### ثانياً: في بيئة PHP العادية (Pure PHP)

يمكنك إنشاء كائن `MgwaClient` مباشرة بيمين الرابط ومفتاح الـ API Token:

```php
use Mgwa\Client\MgwaClient;

require_once __DIR__ . '/vendor/autoload.php';

$client = new MgwaClient(
    baseUrl: 'https://mgwa.yourdomain.com',
    apiToken: 'your_sanctum_api_token_here',
    timeout: 30
);
```

---

## 📚 دليل استخدام الـ API (API Reference & Methods)

يمثل الكلاس `MgwaClient` المدخل الرئيسي لجميع نقاط الـ API المتاحة في بوابة MGWA:

| اسم الدالة (Method) | المسار (API Endpoint) | الوصف |
| :--- | :--- | :--- |
| `getSessions()` | `GET /api/v1/sessions` | جلب قائمة كافة جلسات الواتساب الخاصة بالحساب |
| `getSessionStatus($session)` | `GET /api/v1/sessions/{session}/status` | معرفة حالة الجلسة (متصل، غير متصل، QR جاهز) |
| `getQrCode($session)` | `GET /api/v1/sessions/{session}/qr` | جلب رمز الـ QR Code بصيغة Base64 للمسح |
| `sendMessage($session, $to, $message, $options)` | `POST /api/v1/sessions/{session}/send-message` | إرسال رسالة نصية أو وسائط عبر الجلسة |
| `sendTextMessage($session, $to, $message, $async)` | `POST /api/v1/sessions/{session}/send-message` | دالة مساعدة لإرسال رسالة نصية مباشرة |
| `sendMediaMessage($session, $to, $fileUrl, ...)` | `POST /api/v1/sessions/{session}/send-message` | دالة مساعدة لإرسال صورة أو ملف PDF أو فيديو |
| `updateWebhook($session, $webhookUrl, $events)` | `POST /api/v1/sessions/{session}/webhook` | تحديث رابط الـ Webhook والأحداث الخاصة بالجلسة |

---

## 💡 أمثلة عملية لاستخدام المكتبة (Code Examples)

### 1. جلب قائمة الجلسات واستعلام الحالة (Get Sessions & Status)

#### استخدام PHP:
```php
use Mgwa\Client\MgwaClient;
use Mgwa\Client\Exceptions\MgwaException;

$client = new MgwaClient('https://mgwa.domain.com', 'API_TOKEN_HERE');

try {
    // جلب جميع الجلسات
    $sessions = $client->getSessions();
    print_r($sessions);

    // استعلام حالة جلسة معينة
    $status = $client->getSessionStatus('session_123');
    echo "حالة الجلسة: " . $status['data']['status']; // connected / disconnected
} catch (MgwaException $e) {
    echo "حدث خطأ: " . $e->getMessage();
}
```

#### استخدام Laravel Facade:
```php
use Mgwa\Client\Laravel\Facades\Mgwa;

// جلب الجلسات
$sessions = Mgwa::getSessions();

// جلب رمز QR
$qrData = Mgwa::getQrCode('my_session_identifier');
$qrBase64 = $qrData['data']['qr_code'];
```

---

### 2. إرسال الرسائل النصية (Send Text Messages)

#### إرسال فوري (Synchronous):
```php
use Mgwa\Client\Laravel\Facades\Mgwa;

$response = Mgwa::sendTextMessage(
    session: 'sess_default',
    to: '201012345678', // الرقم بالصيغة الدولية
    message: 'مرحباً بك! هذه رسالة ترحيبية من بوابة MGWA 🚀'
);

if ($response['status']) {
    echo "تم إرسال الرسالة بنجاح!";
}
```

#### إرسال خلفي غير متزامن عبر الطابور (Async / Queued):
عند الإرسال المجمع أو حملات التسويق، يُفضل تفعيل وضع `async`:

```php
$response = Mgwa::sendTextMessage(
    session: 'sess_default',
    to: '201012345678',
    message: 'إشعار تذكيري بموعد الفاتورة',
    async: true // إرسال في الخلفية عبر طابور النظام
);
```

---

### 3. إرسال الوسائط والملفات (Send Images, PDFs, Documents)

```php
use Mgwa\Client\Laravel\Facades\Mgwa;

// 1. إرسال صورة مع نص مصاحب
Mgwa::sendMediaMessage(
    session: 'sess_default',
    to: '201012345678',
    fileUrl: 'https://example.com/invoice.jpg',
    caption: 'تفاصيل الفاتورة الخاصة بك رقم #1024',
    type: 'image'
);

// 2. إرسال ملف PDF
Mgwa::sendMediaMessage(
    session: 'sess_default',
    to: '201012345678',
    fileUrl: 'https://example.com/files/report.pdf',
    caption: 'تقرير المبيعات الشهري',
    filename: 'Monthly_Report.pdf',
    type: 'document',
    async: true
);
```

---

### 4. تحديث رابط الـ Webhook للجلسة (Update Webhook Settings)

```php
use Mgwa\Client\Laravel\Facades\Mgwa;

Mgwa::updateWebhook(
    session: 'sess_default',
    webhookUrl: 'https://my-app.com/api/whatsapp-webhook',
    events: [
        'message.received', // استقبال الرسائل الواردة
        'message.sent',     // الرسائل المرسلة
        'message.ack',      // تحديثات حالة الاستلام والقراءة (Read/Delivered)
        'session.status',   // تغيرات حالة الجلسة (اتصال/انقطاع)
    ]
);
```

---

## 🔔 استقبال ومعالجة الـ Webhook (Receiving Incoming Webhooks)

توفر المكتبة كلاس `WebhookHandler` للتحقق من سلامة الطلب المرسل من السيرفر وفك الحمولة بسهولة.

### مثال في Laravel Controller:

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mgwa\Client\WebhookHandler;

class WhatsappWebhookController extends Controller
{
    public function handle(Request $request, WebhookHandler $handler)
    {
        // 1. التحقق من الرمز السري للهيدر (X-Webhook-Secret)
        if (!$handler->verifySecret($request->header('X-Webhook-Secret'))) {
            return response()->json(['status' => false, 'message' => 'غير مصرح بالوصول'], 401);
        }

        // 2. تحليل الحمولة المقروءة
        $parsed = $handler->parse($request->all());

        // 3. معالجة الرسائل الواردة
        if ($parsed['is_message']) {
            $msgData = $handler->extractMessage($parsed);
            
            $sender = $msgData['from'];        // رقم المرسل
            $messageText = $msgData['body'];   // محتوى الرسالة
            $messageType = $msgData['type'];   // نص / صورة / ملف

            // يمكنك هنا حفظ الرسالة في قاعدة البيانات أو الرد التلقائي
            // \Log::info("رسالة جديدة من {$sender}: {$messageText}");
        }

        // 4. معالجة أحداث التوصيل والقراءة (Ack Status)
        if ($parsed['is_status_ack']) {
            $messageId = $parsed['data']['messageId'] ?? null;
            $status = $parsed['data']['status'] ?? null; // sent, delivered, read
        }

        // 5. معالجة التغير في حالة الجلسة
        if ($parsed['is_session']) {
            $event = $parsed['event']; // session.authenticated / session.disconnected
        }

        return response()->json(['status' => true, 'message' => 'OK']);
    }
}
```

### مثال في PHP العادية (Pure PHP File):

```php
use Mgwa\Client\WebhookHandler;
use Mgwa\Client\Exceptions\MgwaException;

$handler = new WebhookHandler(secret: 'YOUR_WEBHOOK_SECRET');

try {
    // التقاط الحمولة تلقائياً من php://input والتحقق من الهيدر
    $parsed = $handler->captureFromGlobals();

    if ($parsed['is_message']) {
        $msg = $handler->extractMessage($parsed);
        file_put_contents('incoming_messages.log', "رسالة من: {$msg['from']} - المحتوى: {$msg['body']}\n", FILE_APPEND);
    }

    http_response_code(200);
    echo json_encode(['status' => true, 'message' => 'Received']);
} catch (MgwaException $e) {
    http_response_code($e->getHttpStatusCode() ?? 400);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
```

---

## 🚨 معالجة الأخطاء (Error Handling)

تطلق كافة دوال المكتبة كائن الاستثناء `MgwaException` عند حدوث أي خطأ في الاتصال أو عدم صحة البيانات:

```php
use Mgwa\Client\MgwaClient;
use Mgwa\Client\Exceptions\MgwaException;

$client = new MgwaClient('https://mgwa.domain.com', 'INVALID_TOKEN');

try {
    $client->sendMessage('sess_1', '201012345678', 'اختبار الرسالة');
} catch (MgwaException $e) {
    echo "نص الخطأ: " . $e->getMessage() . "\n";
    echo "كود حالة HTTP: " . $e->getHttpStatusCode() . "\n";
    
    // استعراض الاستجابة الخام المعادة من الخادم
    print_r($e->getResponseBody());
}
```

---

## 📄 الترخيص (License)

هذه الحزمة مضافة ومفتوحة المصدر بموجب ترخيص [MIT License](LICENSE).
