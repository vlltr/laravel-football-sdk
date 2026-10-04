<?php

declare(strict_types=1);

namespace FootballApi;

use FootballApi\Exceptions\FootballException;

class Football
{
    use MakesHttpRequests;

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
