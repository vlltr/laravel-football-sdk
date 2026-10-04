<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class FixtureStatistics
{
    public function __construct(
        public int $fixtureId,
        public MatchStatistics $statistics,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'fixture_id' => ['type' => 'int', 'required' => true, 'as' => 'fixtureId'],
            'statistics' => ['class' => MatchStatistics::class, 'required' => true],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
