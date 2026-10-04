<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use FootballApi\Support\Hydrator;

final readonly class AccountStatus
{
    public function __construct(
        public string $plan,
        public RateLimits $limits,
        public Usage $usage,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'plan' => ['type' => 'string', 'required' => true],
            'limits' => ['class' => RateLimits::class, 'required' => true],
            'usage' => ['class' => Usage::class, 'required' => true],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
