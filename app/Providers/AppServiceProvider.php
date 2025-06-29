<?php

namespace App\Providers;

use App\Models\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(['app.locale' => env('APP_LOCALE', 'id')]);
        config(['app.timezone' => env('APP_TIMEZONE', 'Asia/Jakarta')]);
        Carbon::setLocale(env('APP_LOCALE', 'id'));
        date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Jakarta'));

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
