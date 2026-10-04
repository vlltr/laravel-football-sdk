<?php

declare(strict_types=1);

namespace FootballApi\Actions;

use FootballApi\Exceptions\FootballException;
use FootballApi\Resources\FixtureOddsBoard;
use FootballApi\Resources\OddsHistoryPage;
use FootballApi\Support\Query;

trait ManagesOdds
{
    /** @param  list<string>|null  $bookmakers */
    public function fixtureOdds(int $id, ?array $bookmakers = null, ?string $market = null): FixtureOddsBoard
    {
        $response = $this->get('fixtures/'.$id.'/odds', Query::make([
            'bookmakers' => $bookmakers,
            'market' => $market,
        ]));

        return FixtureOddsBoard::fromArray($this->dataArray($response));
    }

    public function fixtureOddsHistory(
        int $id,
        string $market,
        ?string $bookmaker = null,
        ?int $page = null,
        ?int $perPage = null,
    ): OddsHistoryPage {
        $response = $this->get('fixtures/'.$id.'/odds/history', Query::make([
            'market' => $market,
            'bookmaker' => $bookmaker,
            'page' => $page,
            'per_page' => $perPage,
        ]));

        if (! is_array($response['pagination'])) {
            throw new FootballException('The API returned an unreadable page.', errorCode: 'invalid_response');
        }

        $paginationPage = (int) ($response['pagination']['page'] ?? 1);

        return OddsHistoryPage::fromArray(
            $this->dataArray($response),
            $response['pagination'],
            fn () => $this->fixtureOddsHistory($id, $market, $bookmaker, $paginationPage + 1, $perPage),
        );
    }
}
