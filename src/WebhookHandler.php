<?php

namespace Mgwa\Client;

use Mgwa\Client\Exceptions\MgwaException;

/**
 * كلاس معالجة المستقبلات والأحداث الواردة عبر الـ Webhook (Webhook Receiver & Handler)
 *
 * يتيح هذا الكلاس استقبال، فحص، والتحقق من سلامة البيانات المرسلة من خادم MGWA
 * فور وصول رسالة جديدة أو تغير حالة الجلسة أو استقبال رمز QR جديد.
 */
class WebhookHandler
{
    /**
     * مفتاح التحقق الخفي للـ Webhook (Secret Key)
     * @var string|null
     */
    protected ?string $secret;

    /**
     * إنشاء كائن معالج الـ Webhook
     *
     * @param string|null $secret الرمز السري للتحقق من هويّة خادم MGWA المرسل
     */
    public function __construct(?string $secret = null) {
        $this->secret = $secret;
    }

    /**
     * التحقق من سلامة الطلب عبر موازنة الرمز السري الممرر في الهيدر (Header Verification)
     *
     * @param string|null $headerSecret قيمة الهيدر المرسلة (عادة `X-Webhook-Secret`)
     * @return bool true في حال المطابقة، false في حال الفشل
     */
    public function verifySecret(?string $headerSecret): bool
    {
        if (empty($this->secret)) {
            // إذا لم يتم ضبط رمز سري في التكوين، يتم اعتماد الطلب تلقائياً
            return true;
        }

        return hash_equals($this->secret, (string) $headerSecret);
    }

    /**
     * قراءة وفحص حمولة الـ Webhook المقروءة كـ Array
     *
     * @param array $payload البيانات القادمة في body الـ Webhook
     * @return array مصفوفة مهيكلة تحتوي على تفاصيل الحدث المعالج
     */
    public function parse(array $payload): array
    {
        $event = $payload['event'] ?? $payload['type'] ?? 'unknown';
        $sessionIdentifier = $payload['session_identifier'] ?? null;
        $phoneNumber = $payload['phone_number'] ?? null;
        $timestamp = $payload['timestamp'] ?? date('c');
        $data = $payload['data'] ?? $payload;

        return [
            'event' => $event,
            'session_identifier' => $sessionIdentifier,
            'phone_number' => $phoneNumber,
            'timestamp' => $timestamp,
            'data' => $data,
            'is_message' => $this->isMessageEvent($event),
            'is_session' => $this->isSessionEvent($event),
            'is_status_ack' => $event === 'message.ack',
        ];
    }

    /**
     * التحقق مما إذا كان الحدث يمثل رسالة واردة أو مرسلة
     *
     * @param string $event
     * @return bool
     */
    public function isMessageEvent(string $event): bool
    {
        return in_array($event, ['message.received', 'message.sent', 'message']) 
            || str_starts_with($event, 'message.');
    }

    /**
     * التحقق مما إذا كان الحدث يعبر عن تحديث حالة الجلسة أو توليد رمز QR
     *
     * @param string $event
     * @return bool
     */
    public function isSessionEvent(string $event): bool
    {
        return str_starts_with($event, 'session.') 
            || in_array($event, ['qr', 'status', 'authenticated', 'disconnected']);
    }

    /**
     * استخراج بيانات الرسالة النصية بشكل مبسط من الحمولة
     *
     * @param array $parsedPayload نتيجة دالة `parse()`
     * @return array|null مصفوفة تحتوى على (from, message, type, message_id) أو null إذا لم تكن رسالة
     */
    public function extractMessage(array $parsedPayload): ?array
    {
        if (!$parsedPayload['is_message']) {
            return null;
        }

        $data = $parsedPayload['data'];

        return [
            'message_id' => $data['id'] ?? $data['messageId'] ?? null,
            'from'       => $data['from'] ?? $data['chatId'] ?? null,
            'to'         => $data['to'] ?? null,
            'body'       => $data['body'] ?? $data['message'] ?? null,
            'type'       => $data['type'] ?? 'text',
            'from_me'    => $data['fromMe'] ?? ($parsedPayload['event'] === 'message.sent'),
            'timestamp'  => $parsedPayload['timestamp'],
        ];
    }

    /**
     * استقبال وقراءة حمولة الـ Request في بيئات PHP العادية (Standard PHP Input Stream)
     *
     * @return array
     * @throws MgwaException
     */
    public function captureFromGlobals(): array
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);

        if (!is_array($decoded)) {
            throw new MgwaException('حمولة الـ Webhook غير صالحة كـ JSON', 400);
        }

        // فحص الـ Header للتحقق من السر إن وجد
        $headerSecret = $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? null;
        if (!$this->verifySecret($headerSecret)) {
            throw new MgwaException('فشل التوثيق: رمز X-Webhook-Secret غير متطابق', 401);
        }

        return $this->parse($decoded);
    }
}
