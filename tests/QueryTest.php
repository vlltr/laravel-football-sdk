<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Support\Query;

class QueryTest extends TestCase
{
    public function test_nulls_are_omitted_and_lists_are_joined(): void
    {
        $query = Query::make([
            'status' => 'live',
            'league' => null,
            'include' => ['events', 'stats'],
            'bookmakers' => ['bet365', 'pinnacle'],
            'popular' => true,
            'esports' => false,
        ]);

        $this->assertSame([
            'status' => 'live',
            'include' => 'events,stats',
            'bookmakers' => 'bet365,pinnacle',
            'popular' => 1,
            'esports' => 'false',
        ], $query);
    }

    public function test_false_popular_is_omitted(): void
    {
        $this->assertSame([], Query::make(['popular' => false]));
        $this->assertSame([], Query::make(['popular' => null]));
    }
}
