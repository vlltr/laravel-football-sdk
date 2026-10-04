<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use Closure;
use FootballApi\Support\Hydrator;

final readonly class OddsHistoryPage
{
    /**
     * @param  list<OddsTick>  $ticks
     * @param  Closure(): ?self  $next
     */
    public function __construct(
        public int $fixtureId,
        public Bookmaker $bookmaker,
        public string $market,
        public array $ticks,
        public int $page,
        public int $perPage,
        public int $count,
        public bool $hasMore,
        private Closure $next,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public static function keys(): array
    {
        return [
            'fixture_id' => ['type' => 'int', 'required' => true],
            'bookmaker' => ['class' => Bookmaker::class, 'required' => true],
            'market' => ['type' => 'string', 'required' => true],
            'ticks' => ['list' => OddsTick::class, 'required' => true],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public static function paginationKeys(): array
    {
        return [
            'page' => ['type' => 'int', 'required' => true],
            'per_page' => ['type' => 'int', 'required' => true, 'as' => 'perPage'],
            'count' => ['type' => 'int', 'required' => true],
            'has_more' => ['type' => 'bool', 'required' => true, 'as' => 'hasMore'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $pagination
     * @param  Closure(): ?self  $next
     */
    public static function fromArray(array $data, array $pagination, Closure $next): self
    {
        return new self(
            ...Hydrator::read($data, self::keys()),
            ...Hydrator::read($pagination, self::paginationKeys()),
            next: $next,
        );
    }

    public function next(): ?self
    {
        return $this->hasMore ? ($this->next)() : null;
    }
}
