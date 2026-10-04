<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Football;
use FootballApi\Resources\AccountStatus;
use FootballApi\Resources\Bookmaker;
use FootballApi\Resources\Country;
use FootballApi\Resources\Fixture;
use FootballApi\Resources\FixtureEvents;
use FootballApi\Resources\FixtureOddsBoard;
use FootballApi\Resources\FixtureStatistics;
use FootballApi\Resources\League;
use FootballApi\Resources\OddsHistoryPage;
use FootballApi\Resources\Page;
use FootballApi\Resources\Standings;
use FootballApi\Resources\Team;
use FootballApi\Support\Contract;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class ClientTest extends TestCase
{
    private function client(): Football
    {
        return new Football('fb_live_test', (string) config('football.base_url'), 10);
    }

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

    /** @return array<string, mixed> */
    private function loadOk(string $file): array
    {
        return ['success' => 1] + $this->load($file);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function paged(array $items, int $page, bool $hasMore): array
    {
        return [
            'success' => 1,
            'data' => $items,
            'pagination' => ['page' => $page, 'per_page' => 50, 'count' => count($items), 'has_more' => $hasMore],
        ];
    }

    private function assertSentPath(string $path): void
    {
        Http::assertSent(function (Request $request) use ($path) {
            return parse_url($request->url(), PHP_URL_PATH) === '/v1/'.$path;
        });
    }

    public function test_fixtures_send_filters_and_return_a_page_of_fixtures(): void
    {
        $item = $this->load('fixture-list.json')['data'];
        Http::fake(['*' => Http::response($this->paged([$item], 1, true))]);

        $page = $this->client()->fixtures(status: 'live', include: ['odds']);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertInstanceOf(Fixture::class, $page->items[0]);
        $this->assertSame(1, $page->page);
        $this->assertSame(50, $page->perPage);
        $this->assertSame(1, $page->count);
        $this->assertTrue($page->hasMore);
        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return parse_url($request->url(), PHP_URL_PATH) === '/v1/fixtures'
                && $query['status'] === 'live'
                && $query['include'] === 'odds';
        });
    }

    public function test_fixtures_use_api_query_names(): void
    {
        Http::fake(['*' => Http::response($this->paged([], 1, false))]);

        $this->client()->fixtures(startTime: 10, endTime: 20, league: 5, esports: false, lang: 'es', page: 2, perPage: 25);

        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $query === [
                'start_time' => '10',
                'end_time' => '20',
                'league' => '5',
                'esports' => 'false',
                'lang' => 'es',
                'page' => '2',
                'per_page' => '25',
            ];
        });
    }

    public function test_next_requests_the_following_page_and_stops_at_the_end(): void
    {
        $item = $this->load('fixture-list.json')['data'];
        Http::fake(['*' => Http::sequence()
            ->push($this->paged([$item], 1, true))
            ->push($this->paged([$item], 2, false))]);

        $first = $this->client()->fixtures(status: 'live');
        $second = $first->next();

        $this->assertInstanceOf(Page::class, $second);
        $this->assertSame(2, $second->page);
        $this->assertNull($second->next());
        Http::assertSentCount(2);
        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['page'] ?? null) === '2' && $query['status'] === 'live';
        });
    }

    public function test_odds_history_returns_a_page_that_can_advance(): void
    {
        Http::fake(['*' => Http::response($this->load('odds-history.json'))]);

        $history = $this->client()->fixtureOddsHistory(1, market: 'asian');

        $this->assertInstanceOf(OddsHistoryPage::class, $history);
        $this->assertSame('asian', $history->market);
        $this->assertSame(481163241, $history->fixtureId);
        $this->assertSame(1, $history->page);
        $this->assertSentPath('fixtures/1/odds/history');

        $history->next();

        Http::assertSentCount(2);
        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['page'] ?? null) === '2' && $query['market'] === 'asian';
        });
    }

    public function test_bookmakers_return_a_list_without_pagination(): void
    {
        $body = $this->load('bookmakers.json');
        Contract::assertMapped($body['data'][0], Bookmaker::keys());
        Http::fake(['*' => Http::response($body)]);

        $bookmakers = $this->client()->bookmakers();

        $this->assertCount(1, $bookmakers);
        $this->assertInstanceOf(Bookmaker::class, $bookmakers[0]);
        $this->assertSame('bet365', $bookmakers[0]->slug);
        $this->assertSentPath('bookmakers');
    }

    public function test_countries_are_cached(): void
    {
        $body = $this->loadOk('countries.json');
        Contract::assertMapped($body['data'][0], Country::keys());
        Http::fake(['*' => Http::response($body)]);

        $first = $this->client()->countries();
        $second = $this->client()->countries();

        $this->assertInstanceOf(Country::class, $first->items[0]);
        $this->assertSame('WORLD', $second->items[0]->code);
        $this->assertNull($second->next());
        Http::assertSentCount(1);
        $this->assertSentPath('countries');
    }

    public function test_status_returns_account_status(): void
    {
        Http::fake(['*' => Http::response($this->loadOk('status.json'))]);

        $status = $this->client()->status();

        $this->assertInstanceOf(AccountStatus::class, $status);
        $this->assertSame('pro', $status->plan);
        $this->assertSentPath('status');
    }

    public function test_fixture_endpoints_hit_their_paths(): void
    {
        $item = $this->load('fixture-list.json')['data'];
        Http::fake(['*' => Http::response($this->paged([$item], 1, false))]);

        $this->assertInstanceOf(Fixture::class, $this->client()->leagueFixtures(7, season: '26/27')->items[0]);
        $this->assertSentPath('leagues/7/fixtures');

        $this->assertInstanceOf(Fixture::class, $this->client()->teamFixtures(8, status: 'finished')->items[0]);
        $this->assertSentPath('teams/8/fixtures');
    }

    public function test_single_fixture_endpoint(): void
    {
        Http::fake(['*' => Http::response($this->loadOk('fixture.json'))]);

        $this->assertInstanceOf(Fixture::class, $this->client()->fixture(422841891, include: ['odds', 'events']));
        $this->assertSentPath('fixtures/422841891');
        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'include=odds%2Cevents'));
    }

    public function test_odds_endpoint_joins_bookmakers(): void
    {
        Http::fake(['*' => Http::response($this->load('odds.json'))]);

        $board = $this->client()->fixtureOdds(422841891, bookmakers: ['bet365', 'pinnacle'], market: '1x2');

        $this->assertInstanceOf(FixtureOddsBoard::class, $board);
        $this->assertSentPath('fixtures/422841891/odds');
        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $query === ['bookmakers' => 'bet365,pinnacle', 'market' => '1x2'];
        });
    }

    public function test_events_and_statistics_endpoints(): void
    {
        $events = $this->load('events.json');
        $statistics = $this->load('statistics.json');
        Contract::assertMapped($events['data'], FixtureEvents::keys());
        Contract::assertMapped($statistics['data'], FixtureStatistics::keys());
        Http::fake([
            '*/events' => Http::response($events),
            '*/statistics' => Http::response($statistics),
        ]);

        $this->assertInstanceOf(FixtureEvents::class, $this->client()->fixtureEvents(422841891));
        $this->assertSentPath('fixtures/422841891/events');

        $this->assertInstanceOf(FixtureStatistics::class, $this->client()->fixtureStatistics(422841891));
        $this->assertSentPath('fixtures/422841891/statistics');
    }

    public function test_standings_endpoint(): void
    {
        Http::fake(['*' => Http::response($this->loadOk('standings-corner.json'))]);

        $standings = $this->client()->standings(3120672213, season: '26/27', type: 'corner', lang: 'en');

        $this->assertInstanceOf(Standings::class, $standings);
        $this->assertSentPath('standings');
        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $query === ['league' => '3120672213', 'season' => '26/27', 'type' => 'corner', 'lang' => 'en'];
        });
    }

    public function test_leagues_and_league_endpoints(): void
    {
        $leagues = $this->load('leagues.json')['data'];
        Http::fake([
            '*/leagues/*' => Http::response($this->loadOk('league.json')),
            '*/leagues*' => Http::response($this->paged($leagues, 1, false)),
        ]);

        $page = $this->client()->leagues(popular: true, country: 'GB-ENG', include: ['seasons'], activeSince: 100, esports: true);

        $this->assertInstanceOf(League::class, $page->items[0]);
        $this->assertSentPath('leagues');
        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return parse_url($request->url(), PHP_URL_PATH) === '/v1/leagues'
                && $query === [
                    'popular' => '1',
                    'country' => 'GB-ENG',
                    'include' => 'seasons',
                    'active_since' => '100',
                    'esports' => 'true',
                ];
        });

        $this->assertInstanceOf(League::class, $this->client()->league(3120672213));
        $this->assertSentPath('leagues/3120672213');
    }

    public function test_popular_false_omits_the_parameter(): void
    {
        Http::fake(['*' => Http::response($this->paged([], 1, false))]);

        $this->client()->leagues(popular: false);

        Http::assertSent(fn (Request $request) => ! str_contains($request->url(), 'popular'));
    }

    public function test_team_endpoint(): void
    {
        Http::fake(['*' => Http::response(['success' => 1, 'data' => ['id' => 7, 'name' => 'Arsenal']])]);

        $team = $this->client()->team(7);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertSame('Arsenal', $team->name);
        $this->assertSentPath('teams/7');
    }
}
