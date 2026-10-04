<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class RateLimits
{
    public function __construct(
        public int $rateLimit,
        public int $rateWindowSeconds,
        public ?int $burstPerMinute,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'rate_limit' => ['type' => 'int', 'required' => true, 'as' => 'rateLimit'],
            'rate_window_seconds' => ['type' => 'int', 'required' => true, 'as' => 'rateWindowSeconds'],
            'burst_per_minute' => ['type' => 'int', 'as' => 'burstPerMinute'],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
