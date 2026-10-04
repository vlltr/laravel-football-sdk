<?php

declare(strict_types=1);

namespace FootballApi;

class FootballManager
{
    public function client(?string $apiKey = null): Football
    {
        return new Football(
            $apiKey ?? (string) config('football.api_key'),
            (string) config('football.base_url'),
            (int) config('football.timeout'),
        );
    }
}
