<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EncryptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('encrypt', function () {
            return function ($data) {
                $secretKey = env('APP_KEY');
                $cipher = "AES-256-CBC";
                $iv = substr(hash('sha256', $secretKey), 0, 16);
                return openssl_encrypt($data, $cipher, $secretKey, 0, $iv);
            };
        });

        $this->app->singleton('decrypt', function () {
            return function ($encrypted) {
                $secretKey = env('APP_KEY');
                $cipher = "AES-256-CBC";
                $iv = substr(hash('sha256', $secretKey), 0, 16);
                return openssl_decrypt($encrypted, $cipher, $secretKey, 0, $iv);
            };
        });
    }

    public function boot()
    {
        //
    }
}
