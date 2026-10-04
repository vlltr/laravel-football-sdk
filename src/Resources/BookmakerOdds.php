<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class BookmakerOdds
{
    public function __construct(
        public string $name,
        public string $slug,
        public BookmakerMarkets $odds,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'name' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'required' => true],
            'odds' => ['class' => BookmakerMarkets::class, 'required' => true],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
