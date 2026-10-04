<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use Carbon\CarbonImmutable;
use FootballApi\Resources\Bookmaker;
use FootballApi\Resources\FixtureOddsBoard;
use FootballApi\Resources\OddsHistoryPage;
use FootballApi\Resources\OddsTick;
use FootballApi\Support\Contract;

class OddsResourceTest extends TestCase
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

    public function test_odds_board_maps_every_market(): void
    {
        $data = $this->load('odds.json')['data'];

        Contract::assertMapped($data, FixtureOddsBoard::keys());

        $board = FixtureOddsBoard::fromArray($data);

        $this->assertSame(422841891, $board->fixtureId);
        $this->assertCount(1, $board->bookmakers);
        $this->assertSame('bet365', $board->bookmakers[0]->slug);
        $this->assertSame(1.53, $board->bookmakers[0]->odds->oneX2->opening->home);
        $this->assertNull($board->bookmakers[0]->odds->oneX2->inplay);
        $this->assertSame(-0.25, $board->bookmakers[0]->odds->asianHandicap->opening->line);
        $this->assertSame(2.75, $board->bookmakers[0]->odds->goalLine->closing->line);
        $this->assertSame(10.0, $board->bookmakers[0]->odds->cornerLine->closing->line);
        $this->assertNull($board->bookmakers[0]->odds->cornerAsian->closing);
        $this->assertSame(1.8, $board->bookmakers[0]->odds->btts->opening->yes);
        $this->assertSame(2.05, $board->bookmakers[0]->odds->btts->closing->no);
    }

    public function test_odds_history_page_maps_ticks_and_pages_forward(): void
    {
        $payload = $this->load('odds-history.json');
        $data = $payload['data'];

        Contract::assertMapped($data['bookmaker'], Bookmaker::keys());
        foreach ($data['ticks'] as $tick) {
            Contract::assertMapped($tick, OddsTick::keys());
        }

        $following = null;
        $page = OddsHistoryPage::fromArray($data, $payload['pagination'], function () use (&$following) {
            return $following;
        });

        $this->assertSame(481163241, $page->fixtureId);
        $this->assertSame('bet365', $page->bookmaker->slug);
        $this->assertSame('asian', $page->market);
        $this->assertSame(1, $page->page);
        $this->assertSame(10, $page->perPage);
        $this->assertSame(1, $page->count);
        $this->assertTrue($page->hasMore);
        $this->assertCount(1, $page->ticks);
        $this->assertNull($page->ticks[0]->minute);
        $this->assertSame(-0.75, $page->ticks[0]->line);
        $this->assertSame(1.8, $page->ticks[0]->home);
        $this->assertNull($page->ticks[0]->draw);
        $this->assertEquals(CarbonImmutable::parse('2025-06-21T01:33:14+00:00'), $page->ticks[0]->recordedAt);
        $this->assertNull($page->next());
    }

    public function test_next_returns_null_without_calling_closure_when_no_more_pages(): void
    {
        $called = false;
        $page = OddsHistoryPage::fromArray(
            [
                'fixture_id' => 1,
                'bookmaker' => ['name' => 'Bet 365', 'slug' => 'bet365'],
                'market' => 'asian',
                'ticks' => [],
            ],
            ['page' => 1, 'per_page' => 10, 'count' => 0, 'has_more' => false],
            function () use (&$called) {
                $called = true;

                return null;
            },
        );

        $this->assertNull($page->next());
        $this->assertFalse($called);
    }
}
