<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class NumericLine
{
    public function __construct(
        public ?float $opening,
        public ?float $closing,
        public ?float $inplay,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'opening' => ['type' => 'float'],
            'closing' => ['type' => 'float'],
            'inplay' => ['type' => 'float'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
