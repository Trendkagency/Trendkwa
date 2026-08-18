<?php

namespace Mgwa\Client\Exceptions;

use Exception;
use Throwable;

/**
 * فئة استثناء مخصصة لإدارة أخطاء التعامل مع بوابة واتساب MGWA
 * 
 * تستخدم هذه الفئة لالتقاط وتصنيف أي أخطاء تحدث أثناء الاتصال بالـ API
 * مثل: مفتاح API غير صالح، جلسة غير متصلة، رقم هاتف خاطئ، أو مشكلات الشبكة.
 */
class MgwaException extends Exception
{
    /**
     * كود استجابة HTTP (إن وجد)
     *
     * @var int|null
     */
    protected ?int $httpStatusCode;

    /**
     * الاستجابة الخام المعادة من خادم الـ API (إن وجدت)
     *
     * @var array|null
     */
    protected ?array $responseBody;

    /**
     * إنشاء كائن جديد من كلاس الاستثناء
     *
     * @param string $message رسالة الخطأ بالعربية أو الإنجليزية
     * @param int $code كود الخطأ البرمجي
     * @param int|null $httpStatusCode كود حالة HTTP مثل 401 أو 422 أو 500
     * @param array|null $responseBody مصفوفة البيانات المعادة من الـ API
     * @param Throwable|null $previous الاستثناء السابق في حال وجوده
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?int $httpStatusCode = null,
        ?array $responseBody = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->httpStatusCode = $httpStatusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * الحصول على كود حالة الـ HTTP
     *
     * @return int|null
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * الحصول على محتوى الاستجابة الخام القادمة من السيرفر
     *
     * @return array|null
     */
    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }

    /**
     * إنشاء استثناء ناتج عن عدم صلاحية الوصول (401 / 403 Unauthorized)
     *
     * @param string|null $customMessage
     * @return static
     */
    public static function unauthorized(?string $customMessage = null): static
    {
        return new static(
            $customMessage ?? 'غير مصرح بالوصول: مفتاح الـ API Token غير صحيح أو منتهي الصلاحية.',
            401,
            401
        );
    }

    /**
     * إنشاء استثناء ناتج عن جلسة غير متصلة (Session Disconnected)
     *
     * @param string $sessionIdentifier معرّف الجلسة
     * @return static
     */
    public static function sessionNotConnected(string $sessionIdentifier): static
    {
        return new static(
            "جلسة الواتساب '{$sessionIdentifier}' غير متصلة حالياً. يرجى مسح رمز QR أولاً.",
            422,
            422
        );
    }

    /**
     * إنشاء استثناء ناتج عن عدم العثور على الجلسة (404 Not Found)
     *
     * @param string $sessionIdentifier
     * @return static
     */
    public static function sessionNotFound(string $sessionIdentifier): static
    {
        return new static(
            "لم يتم العثور على جلسة الواتساب المحددة: '{$sessionIdentifier}'.",
            404,
            404
        );
    }

    /**
     * إنشاء استثناء ناتج عن خطأ في المدخلات (Validation Error 422)
     *
     * @param string $message
     * @param array|null $errors
     * @return static
     */
    public static function invalidPayload(string $message, ?array $errors = null): static
    {
        return new static(
            "خطأ في بيانات الطلب: {$message}",
            422,
            422,
            $errors
        );
    }
}
