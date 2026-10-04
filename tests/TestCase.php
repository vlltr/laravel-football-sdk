<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\FootballServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [FootballServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('football.api_key', 'fb_live_test');
        $app['config']->set('cache.default', 'array');
    }
}
