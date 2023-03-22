<?php

use Illuminate\Support\Facades\Route;
use Larapkg\LaravelSocialUser\Http\Controllers\SocialProviderController;

if (config('laravel-social-provider.routes.enabled')) {
    Route::post(
        sprintf('%s/{provider}', config('laravel-social-provider.route_name')),
        [
            SocialProviderController::class,
            'socialLogin'
        ],
    )->prefix(config('laravel-social-provider.route_prefix'));
}
