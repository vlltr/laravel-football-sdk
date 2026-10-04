<?php

declare(strict_types=1);

namespace FootballApi\Support;

use Carbon\CarbonImmutable;
use FootballApi\Exceptions\FootballException;
use Throwable;

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

            return array_map(function ($item) use ($key, $spec) {
                if (! is_array($item)) {
                    throw self::invalid("Invalid item in {$key}.");
                }

                return $spec['list']::fromArray($item);
            }, array_values($value));
        }

        if (isset($spec['class'])) {
            if (! is_array($value)) {
                throw self::invalid("Invalid {$key}.");
            }

            return $spec['class']::fromArray($value);
        }

        $type = $spec['type'] ?? null;

        if (in_array($type, ['int', 'float', 'string', 'bool'], true) && ! is_scalar($value)) {
            throw self::invalid("Invalid {$key}.");
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'string' => (string) $value,
            'bool' => (bool) $value,
            'datetime' => self::datetime($key, $value),
            default => $value,
        };
    }

    private static function datetime(string $key, mixed $value): CarbonImmutable
    {
        if (! is_string($value)) {
            throw self::invalid("Invalid {$key}.");
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            throw self::invalid("Invalid {$key}.");
        }
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
