<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class HandicapLine
{
    public function __construct(
        public ?HandicapQuote $opening,
        public ?HandicapQuote $closing,
        public ?HandicapQuote $inplay,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'opening' => ['class' => HandicapQuote::class],
            'closing' => ['class' => HandicapQuote::class],
            'inplay' => ['class' => HandicapQuote::class],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
