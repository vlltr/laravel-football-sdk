<?php

declare(strict_types=1);

namespace FootballApi;

use Illuminate\Support\ServiceProvider;

class FootballServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/football.php', 'football');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/football.php' => config_path('football.php'),
        ], 'football-config');
    }
}
