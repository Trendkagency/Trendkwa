<?php

namespace Mgwa\Client\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Mgwa\Client\MgwaClient;

/**
 * الواجهة السريعة (Laravel Facade) لاستخدام كائن MgwaClient بسهولة
 * 
 * @method static array getSessions() جلب جميع الجلسات
 * @method static array getSessionStatus(string $session) الاستعلام عن حالة جلسة
 * @method static array getQrCode(string $session) جلب رمز QR لجلسة
 * @method static array waitForQr(string $session, int $attempts = 15, int $delayMs = 800) انتظار ظهور رمز الـ QR
 * @method static bool sessionIsRunning(?array $session) هل الجلسة متصلة أو تقلع أو تنتظر QR
 * @method static array sendMessage(string $session, string $to, string $message, array $options = []) إرسال رسالة نصية أو وسائط
 * @method static array sendTextMessage(string $session, string $to, string $message, bool $async = false) إرسال رسالة نصية بسيطة
 * @method static array sendMediaMessage(string $session, string $to, string $fileUrl, ?string $caption = null, ?string $filename = null, string $type = 'image', bool $async = false) إرسال صورة أو ملف
 * @method static array updateWebhook(string $session, string $webhookUrl, array $events = []) تحديث رابط الـ Webhook للجلسة
 * @method static array createSession(string $identifier, array $options = []) إنشاء جلسة وبدء المحرك
 * @method static array|null findSession(string $identifier) البحث عن جلسة بالمعرّف
 * @method static array startSession(string $session) بدء محرك الجلسة لتوليد QR
 * @method static array checkNumber(string $session, string $phone) التحقق من الرقم على واتساب
 * 
 * @see \Mgwa\Client\MgwaClient
 */
class Mgwa extends Facade
{
    /**
     * الحصول على الاسم المكون المسجل في الحاوية (Container Component Name)
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return MgwaClient::class;
    }
}
