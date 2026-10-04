<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use Carbon\CarbonImmutable;
use FootballApi\Support\Hydrator;

final readonly class League
{
    public function __construct(
        public int $id,
        public string $name,
        public string $shortName,
        public Country $country,
        public bool $isPopular,
        public bool $hasStandings,
        public CarbonImmutable $lastFixtureAt,
        public int $lastFixtureTs,
        /** @var list<Season> */
        public array $seasons,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'id' => ['type' => 'int', 'required' => true],
            'name' => ['type' => 'string', 'required' => true],
            'short_name' => ['type' => 'string', 'required' => true, 'as' => 'shortName'],
            'country' => ['class' => Country::class, 'required' => true],
            'is_popular' => ['type' => 'bool', 'required' => true, 'as' => 'isPopular'],
            'has_standings' => ['type' => 'bool', 'required' => true, 'as' => 'hasStandings'],
            'last_fixture_utc' => ['type' => 'datetime', 'required' => true, 'as' => 'lastFixtureAt'],
            'last_fixture_ts' => ['type' => 'int', 'required' => true, 'as' => 'lastFixtureTs'],
            'seasons' => ['list' => Season::class, 'missing' => []],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
