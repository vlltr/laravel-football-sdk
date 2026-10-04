<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class ThreeWayLine
{
    public function __construct(
        public ?ThreeWayQuote $opening,
        public ?ThreeWayQuote $closing,
        public ?ThreeWayQuote $inplay,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'opening' => ['class' => ThreeWayQuote::class],
            'closing' => ['class' => ThreeWayQuote::class],
            'inplay' => ['class' => ThreeWayQuote::class],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
