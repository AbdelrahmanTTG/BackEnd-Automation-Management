<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\ApiUser;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->register();

        // Auth::extend('api_key', function ($app, $name, array $config) {
        //     return new class($app['request']) {
        //         protected $request;

        //         public function __construct($request)
        //         {
        //             $this->request = $request;
        //         }

        //         public function user()
        //         {
        //             $apiKey = $this->request->header('X-API-KEY');

        //             if (!$apiKey) {
        //                 return null;
        //             }
        //             return ApiUser::where('api_key', $apiKey)->first();
        //         }
        //     };
        // });
    }
}
