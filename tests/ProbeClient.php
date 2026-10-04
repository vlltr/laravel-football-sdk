<?php

declare(strict_types=1);

namespace FootballApi\Tests;

use FootballApi\Football;

class ProbeClient extends Football
{
    /**
     * @param  array<string, mixed>  $query
     * @return array{data: mixed, pagination: ?array<string, mixed>}
     */
    public function raw(string $path, array $query = [], ?string $ttlKey = null): array
    {
        return $this->get($path, $query, $ttlKey);
    }
}
