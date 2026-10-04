<?php

declare(strict_types=1);

namespace FootballApi;

use Illuminate\Support\ServiceProvider;

class FootballServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/football.php', 'football');

        $this->app->singleton(FootballManager::class);
        $this->app->singleton(Football::class, function ($app) {
            return $app->make(FootballManager::class)->client();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/football.php' => config_path('football.php'),
        ], 'football-config');
    }
}
