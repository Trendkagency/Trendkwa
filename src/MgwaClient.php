<?php

namespace Mgwa\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Mgwa\Client\Exceptions\MgwaException;

/**
 * الكلاس الرئيسي للاتصال ببوابة واتساب MGWA (MGWA WhatsApp SDK Client)
 * 
 * يتيح هذا الكلاس إمكانية تنفيذ العمليات التالية عبر API النظام:
 * 1. استعلام الجلسات وحالتها (Sessions & Status)
 * 2. جلب رمز الاستجابة السريعة (QR Code)
 * 3. إرسال الرسائل النصية والوسائط (فورياً أو عبر الطابور Queued/Async)
 * 4. ضبط وتحديث إعدادات الـ Webhook الخاص بكل جلسة
 */
class MgwaClient
{
    /**
     * الرابط الأساسي لخادم الـ API (Base URL)
     * @var string
     */
    protected string $baseUrl;

    /**
     * الرمز السري للتوثيق (Sanctum API Token)
     * @var string
     */
    protected string $apiToken;

    /**
     * عميل Guzzle HTTP للمشابكة مع السيرفر
     * @var GuzzleClient
     */
    protected GuzzleClient $httpClient;

    /**
     * مهلة طلب الـ HTTP بالثواني
     * @var int
     */
    protected int $timeout;

    /**
     * إنشاء كائن جديد من كلاس الاتصال ببوابة MGWA
     *
     * @param string $baseUrl الرابط الرئيسي للخادم (مثال: https://mgwa.example.com)
     * @param string $apiToken مفتاح API Token الخاص بالمستخدم
     * @param int $timeout مهلة الانتظار بالثواني (افتراضياً 30 ثانية)
     * @param GuzzleClient|null $httpClient عميل مخصص (اختياري للاختبارات)
     */
    public function __construct(
        string $baseUrl,
        string $apiToken,
        int $timeout = 30,
        ?GuzzleClient $httpClient = null
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiToken = trim($apiToken);
        $this->timeout = $timeout;

        $this->httpClient = $httpClient ?? new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout'  => $this->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    /**
     * جلب قائمة جميع جلسات الواتساب الخاصة بالمستخدم المتصل
     *
     * GET /api/v1/sessions
     *
     * @return array مصفوفة تحتوي على بيانات الجلسات مثل (id, session_identifier, status, phone_number)
     * @throws MgwaException عند حدوث خطأ في التوثيق أو الاتصال
     */
    public function getSessions(): array
    {
        return $this->request('GET', '/api/v1/sessions');
    }

    /**
     * الاستعلام عن حالة جلسة واتساب محددة
     *
     * GET /api/v1/sessions/{session}/status
     *
     * @param string $session معرّف الجلسة (session_identifier أو ID)
     * @return array مصفوفة تحتوي على حالة الجلسة الحالية (connected, disconnected, qr_pending, failed)
     * @throws MgwaException عند عدم العثور على الجلسة أو تعذر الاتصال
     */
    public function getSessionStatus(string $session): array
    {
        return $this->request('GET', "/api/v1/sessions/{$session}/status");
    }

    /**
     * جلب رمز QR Code الخاص بالجلسة لربط حساب الواتساب
     *
     * GET /api/v1/sessions/{session}/qr
     *
     * @param string $session معرّف الجلسة
     * @return array مصفوفة تحتوي على سلسلة Base64 لرمز الـ QR (`qr_code`)
     * @throws MgwaException
     */
    public function getQrCode(string $session): array
    {
        return $this->request('GET', "/api/v1/sessions/{$session}/qr");
    }

    /**
     * إرسال رسالة نصية أو وسائط عبر جلسة واتساب محددة
     *
     * POST /api/v1/sessions/{session}/send-message
     *
     * @param string $session معرّف الجلسة
     * @param string $to رقم المستلم بالصيغة الدولية بدون (+) (مثال: 201012345678)
     * @param string $message نص الرسالة (حتى 4096 حرف)
     * @param array $options خيارات إضافية للرسالة:
     *   - `async` (bool): إرسال خلفي غير متزامن عبر الطابور (موصى به عند الكثافة)
     *   - `type` (string): نوع الرسالة (text, image, document)
     *   - `file_url` (string): رابط الملف المراد إرساله
     *   - `filename` (string): اسم الملف المراد إظهاره
     * @return array استجابة السيرفر بعد عملية الإرسال
     * @throws MgwaException في حال كانت الجلسة غير متصلة أو الرقم غير صحيح
     */
    public function sendMessage(string $session, string $to, string $message, array $options = []): array
    {
        $payload = array_merge([
            'to' => $this->formatPhoneNumber($to),
            'message' => $message,
        ], $options);

        return $this->request('POST', "/api/v1/sessions/{$session}/send-message", $payload);
    }

    /**
     * دالة مساعدة سريعة لإرسال رسالة نصية بسيطة
     *
     * @param string $session معرّف الجلسة
     * @param string $to رقم المستلم
     * @param string $message نص الرسالة
     * @param bool $async هل يتم الإرسال عبر الطابور بشكل غير متزامن؟
     * @return array
     * @throws MgwaException
     */
    public function sendTextMessage(string $session, string $to, string $message, bool $async = false): array
    {
        return $this->sendMessage($session, $to, $message, [
            'type' => 'text',
            'async' => $async,
        ]);
    }

    /**
     * دالة مساعدة سريعة لإرسال صورة أو ملف وسائط
     *
     * @param string $session معرّف الجلسة
     * @param string $to رقم المستلم
     * @param string $fileUrl رابط الوسائط المباشر
     * @param string|null $caption النص المصاحب للوسائط (اختياري)
     * @param string|null $filename اسم الملف عند التحميل (اختياري)
     * @param string $type نوع الملف ('image' أو 'document' أو 'video')
     * @param bool $async الإرسال عبر الطابور؟
     * @return array
     * @throws MgwaException
     */
    public function sendMediaMessage(
        string $session,
        string $to,
        string $fileUrl,
        ?string $caption = null,
        ?string $filename = null,
        string $type = 'image',
        bool $async = false
    ): array {
        return $this->sendMessage($session, $to, $caption ?? '', [
            'type' => $type,
            'file_url' => $fileUrl,
            'filename' => $filename,
            'async' => $async,
        ]);
    }

    /**
     * تحديث رابط الـ Webhook والأحداث المشترك بها للجلسة
     *
     * POST /api/v1/sessions/{session}/webhook
     *
     * @param string $session معرّف الجلسة
     * @param string $webhookUrl الرابط الذي سيتلقى الإشعارات (URL)
     * @param array $events مصفوفة الأحداث المطلوبة (مثل: ['message.received', 'message.sent', 'session.status'])
     * @return array
     * @throws MgwaException
     */
    public function updateWebhook(string $session, string $webhookUrl, array $events = []): array
    {
        $payload = [
            'webhook_url' => $webhookUrl,
            'webhook_events' => $events,
        ];

        return $this->request('POST', "/api/v1/sessions/{$session}/webhook", $payload);
    }

    /**
     * تنفيذ طلب الـ HTTP ومعالجة الأستجابات واستثناءات الأخطاء
     *
     * @param string $method نوع الطلب (GET, POST, etc.)
     * @param string $uri المسار الفرعي للطلب
     * @param array|null $data البيانات المراد إرسالها مع الطلب
     * @return array الاستجابة المفككة كـ Array
     * @throws MgwaException
     */
    protected function request(string $method, string $uri, ?array $data = null): array
    {
        $options = [];
        if ($data !== null) {
            $options['json'] = $data;
        }

        try {
            $response = $this->httpClient->request($method, ltrim($uri, '/'), $options);
            $contents = (string) $response->getBody();
            $decoded = json_decode($contents, true);

            if (! is_array($decoded)) {
                throw new MgwaException("استجابة غير صالحة من السيرفر: {$contents}", $response->getStatusCode(), $response->getStatusCode());
            }

            // إذا كانت الاستجابة تعبر عن فشل بناءً على حقل status
            if (isset($decoded['status']) && $decoded['status'] === false) {
                $msg = $decoded['message'] ?? 'فشلت العملية على سيرفر MGWA';
                throw new MgwaException($msg, 422, $response->getStatusCode(), $decoded);
            }

            return $decoded;

        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
            $responseBody = null;

            if ($e->hasResponse()) {
                $bodyString = (string) $e->getResponse()->getBody();
                $responseBody = json_decode($bodyString, true) ?? ['raw' => $bodyString];
            }

            $errorMessage = $responseBody['message'] ?? $e->getMessage();

            if ($statusCode === 401 || $statusCode === 403) {
                throw MgwaException::unauthorized($errorMessage);
            }

            if ($statusCode === 404) {
                throw new MgwaException("المسار أو الجلسة غير موجودة: {$errorMessage}", 404, 404, $responseBody, $e);
            }

            throw new MgwaException("خطأ في الاتصال بسيرفر MGWA: {$errorMessage}", $statusCode, $statusCode, $responseBody, $e);

        } catch (GuzzleException $e) {
            throw new MgwaException("فشل الاتصال بالشريكة أو الشبكة: " . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * التنسيق التلقائي لرقم الهاتف لضمان تحويله للصيغة الرقمية الدولية
     *
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // إزالة الأقواس، المسافات، وعلامة (+)
        $cleaned = preg_replace('/[^\d]/', '', $phone);
        return $cleaned;
    }
}
