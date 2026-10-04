<?php

declare(strict_types=1);

namespace FootballApi\Tests;

class PackageTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertSame(
            'https://api.5dollarfootballapi.com/v1',
            config('football.base_url'),
        );
        $this->assertSame(2, config('football.retry.max_wait'));
        $this->assertSame(86400, config('football.cache.ttl.countries'));
    }
}
