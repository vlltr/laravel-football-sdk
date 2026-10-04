<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use Carbon\CarbonImmutable;
use FootballApi\Support\Hydrator;

final readonly class OddsTick
{
    public function __construct(
        public ?int $minute,
        public ?float $line,
        public ?float $home,
        public ?float $draw,
        public ?float $away,
        public ?float $over,
        public ?float $under,
        public ?float $yes,
        public ?float $no,
        public ?Scoreline $score,
        public CarbonImmutable $recordedAt,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'minute' => ['type' => 'int'],
            'line' => ['type' => 'float'],
            'home' => ['type' => 'float'],
            'draw' => ['type' => 'float'],
            'away' => ['type' => 'float'],
            'over' => ['type' => 'float'],
            'under' => ['type' => 'float'],
            'yes' => ['type' => 'float'],
            'no' => ['type' => 'float'],
            'score' => ['class' => Scoreline::class],
            'recorded_at' => ['type' => 'datetime', 'required' => true],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...Hydrator::read($data, self::keys()));
    }
}
