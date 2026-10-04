<?php

declare(strict_types=1);

namespace FootballApi\Resources;

use Closure;
use FootballApi\Exceptions\FootballException;
use FootballApi\Support\Hydrator;

/**
 * @template T
 */
final readonly class Page
{
    /**
     * @param  list<T>  $items
     * @param  Closure(): ?self<T>  $next
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $perPage,
        public int $count,
        public bool $hasMore,
        private Closure $next,
    ) {}

    /**
     * @param  array{data: mixed, pagination: ?array<string, mixed>}  $response
     * @param  Closure(array<string, mixed>): mixed  $map  hydrates one item
     * @param  Closure(int): self  $fetch  loads the given page number
     */
    public static function fromResponse(array $response, Closure $map, Closure $fetch): self
    {
        $data = $response['data'];
        $pagination = $response['pagination'];

        if (! is_array($pagination) || ! is_array($data) || ! array_is_list($data)) {
            throw new FootballException('The API returned an unreadable page.', errorCode: 'invalid_response');
        }

        $meta = Hydrator::read($pagination, self::paginationKeys());

        return new self(
            array_map($map, $data),
            $meta['page'],
            $meta['perPage'],
            $meta['count'],
            $meta['hasMore'],
            fn (): self => $fetch($meta['page'] + 1),
        );
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

    /** @return ?self<T> */
    public function next(): ?self
    {
        return $this->hasMore ? ($this->next)() : null;
    }
}
