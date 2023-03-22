<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelSocialProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-social-provider.php',
            'laravel-social-provider'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-social-provider.php' =>
                config_path('laravel-social-provider.php'),
        ]);
    }
}
