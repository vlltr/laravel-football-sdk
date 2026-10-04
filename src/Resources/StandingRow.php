<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class StandingRow
{
    public function __construct(
        public int $position,
        public Team $team,
        public int $played,
        public ?int $win,
        public ?int $draw,
        public ?int $lose,
        public ?int $points,
        public ?int $totalFor,
        public ?int $totalAgainst,
        public ?float $averageFor,
        public ?float $averageAgainst,
        public ?StandingSplit $firstHalf,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'position' => ['type' => 'int', 'required' => true],
            'team' => ['class' => Team::class, 'required' => true],
            'played' => ['type' => 'int', 'required' => true],
            'win' => ['type' => 'int'],
            'draw' => ['type' => 'int'],
            'lose' => ['type' => 'int'],
            'points' => ['type' => 'int'],
            'total_for' => ['type' => 'int', 'as' => 'totalFor'],
            'total_against' => ['type' => 'int', 'as' => 'totalAgainst'],
            'average_for' => ['type' => 'float', 'as' => 'averageFor'],
            'average_against' => ['type' => 'float', 'as' => 'averageAgainst'],
            'first_half' => ['class' => StandingSplit::class, 'as' => 'firstHalf'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
