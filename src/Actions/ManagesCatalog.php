<?php

declare(strict_types=1);

namespace FootballApi\Actions;

use FootballApi\Resources\Bookmaker;
use FootballApi\Resources\Country;
use FootballApi\Resources\Page;
use FootballApi\Support\Query;

trait ManagesCatalog
{
    /** @return Page<Country> */
    public function countries(
        ?string $search = null,
        ?string $lang = null,
        ?int $page = null,
        ?int $perPage = null,
    ): Page {
        $response = $this->get('countries', Query::make([
            'search' => $search,
            'lang' => $lang,
            'page' => $page,
            'per_page' => $perPage,
        ]), 'countries');

        return Page::fromResponse(
            $response,
            Country::fromArray(...),
            fn (int $next) => $this->countries($search, $lang, $next, $perPage),
        );
    }

    /** @return list<Bookmaker> */
    public function bookmakers(): array
    {
        $response = $this->get('bookmakers', [], 'bookmakers');

        return array_map(Bookmaker::fromArray(...), array_values($this->dataArray($response)));
    }
}
