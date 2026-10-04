<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Resources\AccountStatus;
use FootballApi\Resources\FixtureEvents;
use FootballApi\Resources\FixtureStatistics;
use FootballApi\Resources\League;
use FootballApi\Resources\Standings;
use FootballApi\Support\Contract;

class CatalogResourceTest extends TestCase
{
    /** @return array<string, mixed> */
    private function load(string $file): array
    {
        return json_decode(
            (string) file_get_contents(__DIR__.'/Fixtures/'.$file),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }

    public function test_total_standings_map_the_table(): void
    {
        $data = $this->load('standings-total.json')['data'];

        Contract::assertMapped($data, Standings::keys());

        $standings = Standings::fromArray($data);

        $this->assertSame(3120672213, $standings->leagueId);
        $this->assertSame('total', $standings->type);
        $this->assertNull($standings->season);
        $this->assertSame(38, $standings->round);
        $this->assertSame('Pyunik Yerevan', $standings->table[0]->team->name);
        $this->assertSame(24, $standings->table[0]->win);
        $this->assertNull($standings->table[0]->firstHalf);
    }

    public function test_corner_standings_map_first_half_split(): void
    {
        $data = $this->load('standings-corner.json')['data'];

        Contract::assertMapped($data, Standings::keys());

        $standings = Standings::fromArray($data);

        $this->assertSame('26/27', $standings->season);
        $this->assertSame(2288481746, $standings->leagueSeasonId);
        $this->assertSame('feed', $standings->source);
        $this->assertSame(118, $standings->table[0]->firstHalf->totalFor);
        $this->assertSame(1.9, $standings->table[0]->firstHalf->averageAgainst);
        $this->assertSame(6.6, $standings->table[0]->averageFor);
    }

    public function test_card_standings_keep_float_averages(): void
    {
        $data = $this->load('standings-card.json')['data'];

        Contract::assertMapped($data, Standings::keys());

        $standings = Standings::fromArray($data);

        $this->assertSame(2.46, $standings->table[0]->averageFor);
        $this->assertNull($standings->table[0]->totalAgainst);
    }

    public function test_league_detail_and_list_rows(): void
    {
        $detail = $this->load('league.json')['data'];
        Contract::assertMapped($detail, League::keys());
        $league = League::fromArray($detail);

        $this->assertSame('EPL', $league->shortName);
        $this->assertSame('England', $league->country->name);
        $this->assertSame(1789412400, $league->lastFixtureTs);
        $this->assertCount(3, $league->seasons);
        $this->assertTrue($league->seasons[0]->current);
        $this->assertFalse($league->seasons[1]->current);

        $list = $this->load('leagues.json')['data'];
        foreach ($list as $row) {
            Contract::assertMapped($row, League::keys());
        }
        $leagues = array_map(League::fromArray(...), $list);

        $this->assertSame('La Liga', $leagues[1]->name);
        $this->assertSame([], $leagues[1]->seasons);
    }

    public function test_account_status_allows_null_burst(): void
    {
        $data = $this->load('status.json')['data'];

        Contract::assertMapped($data, AccountStatus::keys());

        $status = AccountStatus::fromArray($data);

        $this->assertSame('pro', $status->plan);
        $this->assertSame(10, $status->limits->rateLimit);
        $this->assertSame(60, $status->limits->rateWindowSeconds);
        $this->assertNull($status->limits->burstPerMinute);
        $this->assertSame(124, $status->usage->today);
    }

    public function test_fixture_events_and_statistics_wrap_fixture_parts(): void
    {
        $fixture = $this->load('fixture.json')['data'];

        $eventsData = ['fixture_id' => $fixture['id'], 'events' => $fixture['events']];
        Contract::assertMapped($eventsData, FixtureEvents::keys());
        $events = FixtureEvents::fromArray($eventsData);

        $this->assertSame($fixture['id'], $events->fixtureId);
        $this->assertCount(count($fixture['events']), $events->events);
        $this->assertSame('goal', $events->events[0]->type);

        $statsData = ['fixture_id' => $fixture['id'], 'statistics' => $fixture['statistics']];
        Contract::assertMapped($statsData, FixtureStatistics::keys());
        $stats = FixtureStatistics::fromArray($statsData);

        $this->assertSame($fixture['id'], $stats->fixtureId);
        $this->assertSame($fixture['statistics']['attacks']['home'], $stats->statistics->attacks->home);
    }
}
