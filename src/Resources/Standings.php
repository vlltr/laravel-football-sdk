<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class Standings
{
    public function __construct(
        public int $leagueId,
        public ?string $season,
        public ?int $leagueSeasonId,
        public string $type,
        public ?string $source,
        public ?int $round,
        /** @var list<StandingRow> */
        public array $table,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'league_id' => ['type' => 'int', 'required' => true, 'as' => 'leagueId'],
            'season' => ['type' => 'string'],
            'league_season_id' => ['type' => 'int', 'as' => 'leagueSeasonId'],
            'type' => ['type' => 'string', 'required' => true],
            'source' => ['type' => 'string'],
            'round' => ['type' => 'int'],
            'table' => ['list' => StandingRow::class, 'required' => true],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
