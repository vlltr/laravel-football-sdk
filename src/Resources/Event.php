<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class Event
{
    public function __construct(
        public string $type,
        public ?int $minute,
        public ?string $team,
        public ?int $count,
        public ?string $period,
        public ?Scoreline $score,
        public ?string $playerIn,
        public ?string $playerOut,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'type' => ['type' => 'string', 'required' => true],
            'minute' => ['type' => 'int'],
            'team' => ['type' => 'string'],
            'count' => ['type' => 'int'],
            'period' => ['type' => 'string'],
            'score' => ['class' => Scoreline::class],
            'player_in' => ['type' => 'string', 'as' => 'playerIn'],
            'player_out' => ['type' => 'string', 'as' => 'playerOut'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
