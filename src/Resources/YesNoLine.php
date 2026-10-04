<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class YesNoLine
{
    public function __construct(
        public ?YesNoQuote $opening,
        public ?YesNoQuote $closing,
        public ?YesNoQuote $inplay,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'opening' => ['class' => YesNoQuote::class],
            'closing' => ['class' => YesNoQuote::class],
            'inplay' => ['class' => YesNoQuote::class],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
