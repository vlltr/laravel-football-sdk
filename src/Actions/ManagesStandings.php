<?php

declare(strict_types=1);

namespace FootballApi\Actions;

use FootballApi\Resources\Standings;
use FootballApi\Support\Query;

trait ManagesStandings
{
    public function standings(
        int $league,
        ?string $season = null,
        ?string $type = null,
        ?string $lang = null,
    ): Standings {
        $response = $this->get('standings', Query::make([
            'league' => $league,
            'season' => $season,
            'type' => $type,
            'lang' => $lang,
        ]));

        return Standings::fromArray($this->dataArray($response));
    }
}
