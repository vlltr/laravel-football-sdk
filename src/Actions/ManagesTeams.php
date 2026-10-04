<?php

declare(strict_types=1);

namespace FootballApi\Actions;

use FootballApi\Resources\Team;
use FootballApi\Support\Query;

trait ManagesTeams
{
    public function team(int $id, ?string $lang = null): Team
    {
        $response = $this->get('teams/'.$id, Query::make(['lang' => $lang]), 'team');

        return Team::fromArray($response['data']);
    }
}
