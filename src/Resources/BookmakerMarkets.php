<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class BookmakerMarkets
{
    public function __construct(
        public ?ThreeWayLine $oneX2,
        public ?HandicapLine $asianHandicap,
        public ?TotalLine $goalLine,
        public ?TotalLine $cornerLine,
        public ?HandicapLine $cornerAsian,
        public ?TotalLine $cardLine,
        public ?HandicapLine $cardAsian,
        public ?HandicapLine $asianHandicapHalf,
        public ?TotalLine $goalLineHalf,
        public ?TotalLine $cornerLineHalf,
        public ?YesNoLine $btts,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            '1x2' => ['class' => ThreeWayLine::class, 'as' => 'oneX2'],
            'asian_handicap' => ['class' => HandicapLine::class],
            'goal_line' => ['class' => TotalLine::class],
            'corner_line' => ['class' => TotalLine::class],
            'corner_asian' => ['class' => HandicapLine::class],
            'card_line' => ['class' => TotalLine::class],
            'card_asian' => ['class' => HandicapLine::class],
            'asian_handicap_half' => ['class' => HandicapLine::class],
            'goal_line_half' => ['class' => TotalLine::class],
            'corner_line_half' => ['class' => TotalLine::class],
            'btts' => ['class' => YesNoLine::class],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
