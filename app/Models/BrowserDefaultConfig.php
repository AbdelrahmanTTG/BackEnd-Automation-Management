<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrowserDefaultConfig extends Model
{
    protected $table = 'browser_default_config';

    protected $fillable = ['config'];

    protected $casts = ['config' => 'array'];

    public static function getConfig(): array
    {
        $row = static::first();
        return $row ? $row->config : static::fallback();
    }

    public static function fallback(): array
    {
        return [
            'headless'             => true,
            'slowMo'               => 0,
            'args'                 => [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-blink-features=AutomationControlled',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--disable-extensions',
                '--disable-background-networking',
                '--disable-sync',
                '--disable-translate',
                '--disable-default-apps',
                '--mute-audio',
                '--no-first-run',
                '--safebrowsing-disable-auto-update',
                '--js-flags=--max-old-space-size=256',
            ],
            'context'              => [
                'userAgent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'viewport'          => ['width' => 1920, 'height' => 1080],
                'ignoreHTTPSErrors' => true,
                'javaScriptEnabled' => true,
                'bypassCSP'         => false,
            ],
            'defaultTimeout'       => 30000,
            'navigationTimeout'    => 60000,
            'clearCookiesOnStart'  => true,
            'restartAfterCycles'   => 50,
            'disableWebDriver'     => true,
            'enableRequestLogging' => false,
            'enableErrorLogging'   => true,
        ];
    }
}
