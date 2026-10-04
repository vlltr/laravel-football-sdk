<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class MatchStatistics
{
    public function __construct(
        public HomeAway $attacks,
        public HomeAway $dangerousAttacks,
        public HomeAway $shotsOnTarget,
        public HomeAway $shotsOffTarget,
        public HomeAway $possession,
        public ?MatchStatistics $firstHalf,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'attacks' => ['class' => HomeAway::class, 'required' => true],
            'dangerous_attacks' => ['class' => HomeAway::class, 'required' => true, 'as' => 'dangerousAttacks'],
            'shots_on_target' => ['class' => HomeAway::class, 'required' => true, 'as' => 'shotsOnTarget'],
            'shots_off_target' => ['class' => HomeAway::class, 'required' => true, 'as' => 'shotsOffTarget'],
            'possession' => ['class' => HomeAway::class, 'required' => true],
            'first_half' => ['class' => self::class, 'as' => 'firstHalf'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
