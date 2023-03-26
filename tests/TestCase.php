<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Tests;

use Illuminate\Support\Facades\Artisan;
use Larapkg\LaravelSocialUser\Providers\LaravelSocialProviderServiceProvider;
use Laravel\Passport\PassportServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return array<int, string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelSocialProviderServiceProvider::class,
            PassportServiceProvider::class,
            SocialiteServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('passport', require __DIR__ . '/../vendor/laravel/passport/config/passport.php');
        $app['config']->set('services', require __DIR__ . '/../vendor/laravel/socialite/config/services.php');

        Artisan::call('migrate', [
            '--database' => 'sqlite',
            '--path' => 'vendor/laravel/passport/database/migrations',
        ]);

        dd($app['config']);
    }
}
