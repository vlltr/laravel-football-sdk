<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class FixtureOdds
{
    public function __construct(
        public ?ThreeWayLine $oneX2,
        public ?NumericLine $asianHandicap,
        public ?NumericLine $goalLine,
        public ?NumericLine $cornerLine,
        public ?NumericLine $cornerAsian,
        public ?NumericLine $cardLine,
        public ?NumericLine $cardAsian,
        public ?NumericLine $asianHandicapHalf,
        public ?NumericLine $goalLineHalf,
        public ?NumericLine $cornerLineHalf,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            '1x2' => ['class' => ThreeWayLine::class, 'as' => 'oneX2'],
            'asian_handicap' => ['class' => NumericLine::class],
            'goal_line' => ['class' => NumericLine::class],
            'corner_line' => ['class' => NumericLine::class],
            'corner_asian' => ['class' => NumericLine::class],
            'card_line' => ['class' => NumericLine::class],
            'card_asian' => ['class' => NumericLine::class],
            'asian_handicap_half' => ['class' => NumericLine::class],
            'goal_line_half' => ['class' => NumericLine::class],
            'corner_line_half' => ['class' => NumericLine::class],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
