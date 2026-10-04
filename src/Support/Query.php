<?php

declare(strict_types=1);

namespace FootballApi\Support;

class Query
{
    public static function make(array $params): array
    {
        $query = [];

        foreach ($params as $key => $value) {
            if ($value === null || ($key === 'popular' && $value !== true)) {
                continue;
            }

            $query[$key] = match ($key) {
                'include', 'bookmakers' => implode(',', $value),
                'popular' => 1,
                'esports' => $value ? 'true' : 'false',
                default => $value,
            };
        }

        return $query;
    }
}
