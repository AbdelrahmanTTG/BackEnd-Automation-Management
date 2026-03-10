<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrowserConfigSeeder extends Seeder
{
    public function run(): void
    {
        // ── Fields Schema ────────────────────────────────────────────
        DB::table('browser_config_fields')->insert([
            'fields' => json_encode([
                [
                    'key'     => 'headless',
                    'type'    => 'boolean',
                    'label'   => 'Headless Mode',
                    'default' => true,
                ],
                [
                    'key'     => 'slowMo',
                    'type'    => 'number',
                    'label'   => 'Slow Motion (ms)',
                    'default' => 0,
                ],
                [
                    'key'      => 'args',
                    'type'     => 'array',
                    'label'    => 'Chrome Arguments',
                    'default'  => [
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
                ],
                [
                    'key'      => 'context',
                    'type'     => 'object',
                    'label'    => 'Browser Context',
                    'children' => [
                        [
                            'key'     => 'userAgent',
                            'type'    => 'string',
                            'label'   => 'User Agent',
                            'default' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        ],
                        [
                            'key'      => 'viewport',
                            'type'     => 'object',
                            'label'    => 'Viewport',
                            'children' => [
                                [
                                    'key'     => 'width',
                                    'type'    => 'number',
                                    'label'   => 'Width',
                                    'default' => 1920,
                                ],
                                [
                                    'key'     => 'height',
                                    'type'    => 'number',
                                    'label'   => 'Height',
                                    'default' => 1080,
                                ],
                            ],
                        ],
                        [
                            'key'     => 'ignoreHTTPSErrors',
                            'type'    => 'boolean',
                            'label'   => 'Ignore HTTPS Errors',
                            'default' => true,
                        ],
                        [
                            'key'     => 'javaScriptEnabled',
                            'type'    => 'boolean',
                            'label'   => 'JavaScript Enabled',
                            'default' => true,
                        ],
                        [
                            'key'     => 'bypassCSP',
                            'type'    => 'boolean',
                            'label'   => 'Bypass CSP',
                            'default' => false,
                        ],
                    ],
                ],
                [
                    'key'     => 'defaultTimeout',
                    'type'    => 'number',
                    'label'   => 'Default Timeout (ms)',
                    'default' => 30000,
                ],
                [
                    'key'     => 'navigationTimeout',
                    'type'    => 'number',
                    'label'   => 'Navigation Timeout (ms)',
                    'default' => 60000,
                ],
                [
                    'key'     => 'clearCookiesOnStart',
                    'type'    => 'boolean',
                    'label'   => 'Clear Cookies on Start',
                    'default' => true,
                ],
                [
                    'key'     => 'restartAfterCycles',
                    'type'    => 'number',
                    'label'   => 'Restart After N Cycles',
                    'default' => 50,
                ],
                [
                    'key'     => 'disableWebDriver',
                    'type'    => 'boolean',
                    'label'   => 'Disable WebDriver Detection',
                    'default' => true,
                ],
                [
                    'key'     => 'enableRequestLogging',
                    'type'    => 'boolean',
                    'label'   => 'Enable Request Logging',
                    'default' => false,
                ],
                [
                    'key'     => 'enableErrorLogging',
                    'type'    => 'boolean',
                    'label'   => 'Enable Error Logging',
                    'default' => true,
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Default Preset ───────────────────────────────────────────
        DB::table('browser_presets')->insert([
            [
                'name'        => 'Default',
                'description' => 'Balanced configuration for general use',
                'sort_order'  => 0,
                'values'      => json_encode([
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
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'        => 'Stealth',
                'description' => 'Anti-detection optimized for scraping',
                'sort_order'  => 1,
                'values'      => json_encode([
                    'headless' => true,
                    'slowMo'   => 0,
                    'args'     => [
                        '--no-sandbox',
                        '--disable-setuid-sandbox',
                        '--disable-blink-features=AutomationControlled',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-extensions',
                        '--disable-features=site-per-process',
                        '--window-size=1920,1080',
                    ],
                    'context'             => [
                        'userAgent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'viewport'          => ['width' => 1920, 'height' => 1080],
                        'ignoreHTTPSErrors' => true,
                        'javaScriptEnabled' => true,
                        'bypassCSP'         => false,
                    ],
                    'defaultTimeout'      => 30000,
                    'navigationTimeout'   => 60000,
                    'clearCookiesOnStart' => true,
                    'restartAfterCycles'  => 50,
                    'disableWebDriver'    => true,
                    'enableErrorLogging'  => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'        => 'Performance',
                'description' => 'Minimal resources, maximum speed',
                'sort_order'  => 2,
                'values'      => json_encode([
                    'headless' => true,
                    'slowMo'   => 0,
                    'args'     => [
                        '--no-sandbox',
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-extensions',
                        '--js-flags=--max-old-space-size=128',
                    ],
                    'context'             => [
                        'userAgent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'viewport'          => ['width' => 1280, 'height' => 720],
                        'ignoreHTTPSErrors' => true,
                        'javaScriptEnabled' => true,
                        'bypassCSP'         => false,
                    ],
                    'defaultTimeout'      => 20000,
                    'navigationTimeout'   => 40000,
                    'clearCookiesOnStart' => true,
                    'restartAfterCycles'  => 30,
                    'disableWebDriver'    => true,
                    'enableErrorLogging'  => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'        => 'Visual',
                'description' => 'Visible browser for debugging',
                'sort_order'  => 3,
                'values'      => json_encode([
                    'headless' => false,
                    'slowMo'   => 100,
                    'args'     => [
                        '--no-sandbox',
                        '--disable-setuid-sandbox',
                        '--window-size=1920,1080',
                    ],
                    'context'             => [
                        'userAgent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'viewport'          => ['width' => 1920, 'height' => 1080],
                        'ignoreHTTPSErrors' => true,
                        'javaScriptEnabled' => true,
                        'bypassCSP'         => false,
                    ],
                    'defaultTimeout'      => 60000,
                    'navigationTimeout'   => 120000,
                    'clearCookiesOnStart' => false,
                    'restartAfterCycles'  => 10,
                    'disableWebDriver'    => false,
                    'enableErrorLogging'  => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
