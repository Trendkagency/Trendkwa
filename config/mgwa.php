<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MGWA WhatsApp Gateway Configuration - إعدادات بوابة واتساب MGWA
    |--------------------------------------------------------------------------
    |
    | هنا يمكنك ضبط إعدادات الاتصال ببوابة MGWA الخاصة بك.
    | يُفضل إدخال البيانات الحساسة في ملف .env
    |
    */

    /**
     * الرابط الرئيسي لخادم بوابة MGWA (Base URL)
     * مثال: https://mgwa.yourdomain.com
     */
    'base_url' => env('MGWA_BASE_URL', 'http://localhost:8000'),

    /**
     * مفتاح الـ API الخافي (API Token / Sanctum Token)
     * يتم توليده من لوحة تحكم MGWA
     */
    'api_token' => env('MGWA_API_TOKEN', ''),

    /**
     * معرّف الجلسة الافتراضية (Session ID / Session Identifier)
     */
    'default_session' => env('MGWA_DEFAULT_SESSION', ''),

    /**
     * الرمز السري للتحقق من التوقيع المباشر لـ Webhook (إن وجد)
     */
    'webhook_secret' => env('MGWA_WEBHOOK_SECRET', ''),

    /**
     * مهلة الاتصال بالثواني (Timeout)
     */
    'timeout' => env('MGWA_TIMEOUT', 30),

    /**
     * إمكانية إعادة المحاولة عند حدوث شبكة أو خطأ غير متوقع
     */
    'retry' => [
        'times' => 3,
        'sleep' => 100, // milliseconds
    ],
];
