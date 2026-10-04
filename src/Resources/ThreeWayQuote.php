<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class ThreeWayQuote
{
    public function __construct(
        public ?float $home,
        public ?float $draw,
        public ?float $away,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'home' => ['type' => 'float'],
            'draw' => ['type' => 'float'],
            'away' => ['type' => 'float'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
