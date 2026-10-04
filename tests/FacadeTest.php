<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Facades\Football;
use FootballApi\FootballManager;
use Illuminate\Support\Facades\Http;

class FacadeTest extends TestCase
{
    public function test_facade_uses_the_configured_key(): void
    {
        Http::fake([
            '*' => Http::response(['success' => 1, 'data' => ['plan' => 'pro', 'limits' => ['rate_limit' => 10, 'rate_window_seconds' => 60, 'burst_per_minute' => null], 'usage' => ['today' => 1]]]),
        ]);

        $this->assertSame('pro', Football::status()->plan);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer fb_live_test'));
    }

    public function test_manager_can_build_a_client_with_another_key(): void
    {
        Http::fake([
            '*' => Http::response(['success' => 1, 'data' => ['plan' => 'ultra', 'limits' => ['rate_limit' => 40, 'rate_window_seconds' => 60, 'burst_per_minute' => null], 'usage' => ['today' => 1]]]),
        ]);

        $status = $this->app->make(FootballManager::class)->client('fb_live_other')->status();

        $this->assertSame('ultra', $status->plan);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer fb_live_other'));
    }
}
