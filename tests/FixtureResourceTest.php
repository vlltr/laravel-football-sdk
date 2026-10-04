<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use Carbon\CarbonImmutable;
use FootballApi\Resources\Fixture;
use FootballApi\Support\Contract;

class FixtureResourceTest extends TestCase
{
    /** @return array<string, mixed> */
    private function load(string $file): array
    {
        $decoded = json_decode(
            (string) file_get_contents(__DIR__.'/Fixtures/'.$file),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        return $decoded['data'];
    }

    public function test_list_fixture_maps_and_leaves_detail_fields_null(): void
    {
        $data = $this->load('fixture-list.json');

        Contract::assertMapped($data, Fixture::keys());

        $fixture = Fixture::fromArray($data);

        $this->assertSame(422841891, $fixture->id);
        $this->assertSame('USA USL Cup', $fixture->league->name);
        $this->assertSame('Birmingham Legion FC', $fixture->home->name);
        $this->assertSame('Tulsa', $fixture->away->name);
        $this->assertSame(1783814400, $fixture->kickoffTs);
        $this->assertSame(3, $fixture->goals->home);
        $this->assertSame(4, $fixture->cards->away->yellow);
        $this->assertNull($fixture->round);
        $this->assertNull($fixture->odds);
        $this->assertNull($fixture->events);
        $this->assertNull($fixture->statistics);
    }

    public function test_detail_fixture_maps_odds_events_and_statistics(): void
    {
        $data = $this->load('fixture.json');

        Contract::assertMapped($data, Fixture::keys());

        $fixture = Fixture::fromArray($data);

        $this->assertSame(12, $fixture->round);
        $this->assertSame(2288481746, $fixture->leagueSeasonId);
        $this->assertEquals(CarbonImmutable::parse('2026-07-12T00:00:00+00:00'), $fixture->kickoffAt);
        $this->assertSame(1.53, $fixture->odds->oneX2->opening->home);
        $this->assertNull($fixture->odds->oneX2->inplay);
        $this->assertSame(-0.5, $fixture->odds->asianHandicap->opening);
        $this->assertNull($fixture->odds->asianHandicapHalf->opening);
        $this->assertSame(15, $fixture->statistics->firstHalf->attacks->home);
        $this->assertNull($fixture->statistics->firstHalf->firstHalf);
        $this->assertCount(8, $fixture->events);
        $this->assertSame(1, $fixture->events[3]->score->home);
        $this->assertSame('R. Silva', $fixture->events[5]->playerIn);
        $this->assertSame('J. Costa', $fixture->events[5]->playerOut);
    }
}
