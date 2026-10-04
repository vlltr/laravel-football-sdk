<?php

declare(strict_types=1);

namespace FootballApi\Support;

use Carbon\CarbonImmutable;
use FootballApi\Exceptions\FootballException;

class Hydrator
{
    /**
     * @param  array<string, array<string, mixed>>  $keys
     * @return array<string, mixed> constructor arguments keyed by property name
     */
    public static function read(array $data, array $keys): array
    {
        $properties = [];

        foreach ($keys as $key => $spec) {
            $property = $spec['as'] ?? self::camel($key);
            $value = $data[$key] ?? null;

            if ($value === null) {
                if ($spec['required'] ?? false) {
                    throw self::invalid("Missing {$key}.");
                }

                if (isset($spec['shape'], $spec['flatten']) && $spec['flatten']) {
                    $properties = [...$properties, ...self::read([], $spec['shape'])];

                    continue;
                }

                $properties[$property] = $spec['missing'] ?? null;

                continue;
            }

            if (isset($spec['shape'], $spec['flatten']) && $spec['flatten']) {
                if (! is_array($value)) {
                    throw self::invalid("Invalid {$key}.");
                }

                $properties = [...$properties, ...self::read($value, $spec['shape'])];

                continue;
            }

            $properties[$property] = self::convert($key, $value, $spec);
        }

        return $properties;
    }

    private static function convert(string $key, mixed $value, array $spec): mixed
    {
        if (isset($spec['list'])) {
            if (! is_array($value)) {
                throw self::invalid("Invalid {$key}.");
            }

            return array_map(fn ($item) => $spec['list']::fromArray($item), array_values($value));
        }

        if (isset($spec['class'])) {
            if (! is_array($value)) {
                throw self::invalid("Invalid {$key}.");
            }

            return $spec['class']::fromArray($value);
        }

        return match ($spec['type'] ?? null) {
            'int' => (int) $value,
            'float' => (float) $value,
            'string' => (string) $value,
            'bool' => (bool) $value,
            'datetime' => CarbonImmutable::parse($value),
            default => $value,
        };
    }

    private static function camel(string $key): string
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }

    private static function invalid(string $message): FootballException
    {
        return new FootballException($message, errorCode: 'invalid_response');
    }
}
