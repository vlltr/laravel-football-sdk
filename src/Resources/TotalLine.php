<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class TotalLine
{
    public function __construct(
        public ?TotalQuote $opening,
        public ?TotalQuote $closing,
        public ?TotalQuote $inplay,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'opening' => ['class' => TotalQuote::class],
            'closing' => ['class' => TotalQuote::class],
            'inplay' => ['class' => TotalQuote::class],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
