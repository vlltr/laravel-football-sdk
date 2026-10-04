<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class TotalQuote
{
    public function __construct(
        public ?float $line,
        public ?float $over,
        public ?float $under,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'line' => ['type' => 'float'],
            'over' => ['type' => 'float'],
            'under' => ['type' => 'float'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
