<?php

declare(strict_types=1);

namespace FootballApi\Actions;

use FootballApi\Resources\League;
use FootballApi\Resources\Page;
use FootballApi\Support\Query;

trait ManagesLeagues
{
    /**
     * @param  list<string>|null  $include
     * @return Page<League>
     */
    public function leagues(
        ?bool $popular = null,
        ?string $country = null,
        ?string $search = null,
        ?array $include = null,
        ?int $activeSince = null,
        ?bool $esports = null,
        ?string $lang = null,
        ?int $page = null,
        ?int $perPage = null,
    ): Page {
        $response = $this->get('leagues', Query::make([
            'popular' => $popular,
            'country' => $country,
            'search' => $search,
            'include' => $include,
            'active_since' => $activeSince,
            'esports' => $esports,
            'lang' => $lang,
            'page' => $page,
            'per_page' => $perPage,
        ]), 'leagues');

        return Page::fromResponse(
            $response,
            League::fromArray(...),
            fn (int $next) => $this->leagues(
                $popular, $country, $search, $include, $activeSince, $esports, $lang, $next, $perPage,
            ),
        );
    }

    public function league(int $id, ?string $lang = null): League
    {
        $response = $this->get('leagues/'.$id, Query::make(['lang' => $lang]), 'league');

        return League::fromArray($response['data']);
    }
}
