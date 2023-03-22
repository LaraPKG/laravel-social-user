<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelSocialProviderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/laravel-social-user.php' => config_path('laravel-social-user.php'),
            ],
            'config'
        );

        $this->publishes(
            [
                __DIR__ . '/../database/migrations/' => database_path('migrations')
            ],
            'migrations'
        );

        $this->loadRoutesFrom(__DIR__ . '/../routes/laravel-social-user.php');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
