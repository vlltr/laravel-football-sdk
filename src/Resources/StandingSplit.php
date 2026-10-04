<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class StandingSplit
{
    public function __construct(
        public ?int $totalFor,
        public ?int $totalAgainst,
        public ?float $averageFor,
        public ?float $averageAgainst,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'total_for' => ['type' => 'int', 'as' => 'totalFor'],
            'total_against' => ['type' => 'int', 'as' => 'totalAgainst'],
            'average_for' => ['type' => 'float', 'as' => 'averageFor'],
            'average_against' => ['type' => 'float', 'as' => 'averageAgainst'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
