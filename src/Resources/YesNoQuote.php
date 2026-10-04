<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class YesNoQuote
{
    public function __construct(
        public ?float $yes,
        public ?float $no,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'yes' => ['type' => 'float'],
            'no' => ['type' => 'float'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
