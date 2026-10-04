<?php

declare(strict_types=1);

namespace FootballApi;

use FootballApi\Actions\ManagesAccount;
use FootballApi\Actions\ManagesCatalog;
use FootballApi\Actions\ManagesFixtures;
use FootballApi\Actions\ManagesLeagues;
use FootballApi\Actions\ManagesOdds;
use FootballApi\Actions\ManagesStandings;
use FootballApi\Actions\ManagesTeams;
use FootballApi\Exceptions\FootballException;

class Football
{
    use MakesHttpRequests;
    use ManagesAccount;
    use ManagesCatalog;
    use ManagesFixtures;
    use ManagesLeagues;
    use ManagesOdds;
    use ManagesStandings;
    use ManagesTeams;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {
        if (PHP_INT_SIZE < 8) {
            throw new FootballException('64-bit PHP is required.');
        }
    }
}
