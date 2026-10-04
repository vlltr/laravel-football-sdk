<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class HandicapQuote
{
    public function __construct(
        public ?float $line,
        public ?float $home,
        public ?float $away,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'line' => ['type' => 'float'],
            'home' => ['type' => 'float'],
            'away' => ['type' => 'float'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
