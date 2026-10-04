<?php

declare(strict_types=1);

namespace FootballApi\Actions;

use FootballApi\Resources\Fixture;
use FootballApi\Resources\FixtureEvents;
use FootballApi\Resources\FixtureStatistics;
use FootballApi\Resources\Page;
use FootballApi\Support\Query;

trait ManagesFixtures
{
    /**
     * @param  list<string>|null  $include
     * @return Page<Fixture>
     */
    public function fixtures(
        ?int $startTime = null,
        ?int $endTime = null,
        ?int $league = null,
        ?string $status = null,
        ?array $include = null,
        ?bool $esports = null,
        ?string $lang = null,
        ?int $page = null,
        ?int $perPage = null,
    ): Page {
        $response = $this->get('fixtures', Query::make([
            'start_time' => $startTime,
            'end_time' => $endTime,
            'league' => $league,
            'status' => $status,
            'include' => $include,
            'esports' => $esports,
            'lang' => $lang,
            'page' => $page,
            'per_page' => $perPage,
        ]));

        return Page::fromResponse(
            $response,
            Fixture::fromArray(...),
            fn (int $next) => $this->fixtures(
                $startTime, $endTime, $league, $status, $include, $esports, $lang, $next, $perPage,
            ),
        );
    }

    /** @param  list<string>|null  $include */
    public function fixture(int $id, ?array $include = null, ?string $lang = null): Fixture
    {
        $response = $this->get('fixtures/'.$id, Query::make(['include' => $include, 'lang' => $lang]));

        return Fixture::fromArray($this->dataArray($response));
    }

    /**
     * @param  list<string>|null  $include
     * @return Page<Fixture>
     */
    public function leagueFixtures(
        int $id,
        ?string $season = null,
        ?int $startTime = null,
        ?int $endTime = null,
        ?string $status = null,
        ?array $include = null,
        ?string $lang = null,
        ?int $page = null,
        ?int $perPage = null,
    ): Page {
        $response = $this->get('leagues/'.$id.'/fixtures', Query::make([
            'season' => $season,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
            'include' => $include,
            'lang' => $lang,
            'page' => $page,
            'per_page' => $perPage,
        ]));

        return Page::fromResponse(
            $response,
            Fixture::fromArray(...),
            fn (int $next) => $this->leagueFixtures(
                $id, $season, $startTime, $endTime, $status, $include, $lang, $next, $perPage,
            ),
        );
    }

    /**
     * @param  list<string>|null  $include
     * @return Page<Fixture>
     */
    public function teamFixtures(
        int $id,
        ?string $status = null,
        ?int $startTime = null,
        ?int $endTime = null,
        ?array $include = null,
        ?string $lang = null,
        ?int $page = null,
        ?int $perPage = null,
    ): Page {
        $response = $this->get('teams/'.$id.'/fixtures', Query::make([
            'status' => $status,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'include' => $include,
            'lang' => $lang,
            'page' => $page,
            'per_page' => $perPage,
        ]));

        return Page::fromResponse(
            $response,
            Fixture::fromArray(...),
            fn (int $next) => $this->teamFixtures(
                $id, $status, $startTime, $endTime, $include, $lang, $next, $perPage,
            ),
        );
    }

    public function fixtureEvents(int $id): FixtureEvents
    {
        return FixtureEvents::fromArray($this->dataArray($this->get('fixtures/'.$id.'/events')));
    }

    public function fixtureStatistics(int $id): FixtureStatistics
    {
        return FixtureStatistics::fromArray($this->dataArray($this->get('fixtures/'.$id.'/statistics')));
    }
}
