<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class FixtureOddsBoard
{
    /** @param list<BookmakerOdds> $bookmakers */
    public function __construct(
        public int $fixtureId,
        public array $bookmakers,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'fixture_id' => ['type' => 'int', 'required' => true],
            'bookmakers' => ['list' => BookmakerOdds::class, 'required' => true],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
