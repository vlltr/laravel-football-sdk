<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class Scoreline
{
    public function __construct(
        public ?int $home,
        public ?int $away,
        public ?int $halfHome,
        public ?int $halfAway,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'home' => ['type' => 'int'],
            'away' => ['type' => 'int'],
            'half_home' => ['type' => 'int', 'as' => 'halfHome'],
            'half_away' => ['type' => 'int', 'as' => 'halfAway'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
