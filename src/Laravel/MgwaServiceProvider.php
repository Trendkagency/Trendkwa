<?php

namespace Mgwa\Client\Laravel;

use Illuminate\Support\ServiceProvider;
use Mgwa\Client\MgwaClient;
use Mgwa\Client\WebhookHandler;

/**
 * مزود الخدمة الخاص بلارافيل (Laravel Service Provider)
 *
 * يقوم بتسجيل ومشاركة كائنات `MgwaClient` و `WebhookHandler` في الحاوية (Container)
 * ونشر ملف التكوين `config/mgwa.php` لتطبيقات Laravel.
 */
class MgwaServiceProvider extends ServiceProvider
{
    /**
     * تسجيل الخدمات والتثبيتات داخل كبسولة التطبيق (Register Services)
     */
    public function register(): void
    {
        // دمج التكوين الافتراضي لـ mgwa
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/mgwa.php',
            'mgwa'
        );

        // تسجيل Singleton لكائن MgwaClient في الحاوية
        $this->app->singleton(MgwaClient::class, function ($app) {
            $config = $app['config']->get('mgwa');

            return new MgwaClient(
                baseUrl: $config['base_url'] ?? 'http://localhost:8000',
                apiToken: $config['api_token'] ?? '',
                timeout: (int) ($config['timeout'] ?? 30)
            );
        });

        // تسجيل الاختصار الحاوي (Alias)
        $this->app->alias(MgwaClient::class, 'mgwa');

        // تسجيل Singleton لكائن WebhookHandler
        $this->app->singleton(WebhookHandler::class, function ($app) {
            $config = $app['config']->get('mgwa');

            return new WebhookHandler(
                secret: $config['webhook_secret'] ?? null
            );
        });
    }

    /**
     * تنفيذ العمليات بعد تحميل مزودات الخدمة (Boot Services)
     */
    public function boot(): void
    {
        // تمكين إمكانية نشر ملف التكوين عبر أمر vendor:publish
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/mgwa.php' => config_path('mgwa.php'),
            ], 'mgwa-config');
        }
    }
}
