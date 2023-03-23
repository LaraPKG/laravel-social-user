<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Tests;

use Larapkg\LaravelSocialUser\Providers\LaravelSocialProviderServiceProvider;
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
        ];
    }

}
