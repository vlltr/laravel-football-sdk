<?php

declare(strict_types=1);

namespace FootballApi\Support;

use PHPUnit\Framework\Assert;

class Contract
{
    /**
     * Fail when the payload holds a key that the resource does not map.
     *
     * @param  array<string, array<string, mixed>>  $keys
     */
    public static function assertMapped(array $data, array $keys, string $path = '$'): void
    {
        foreach (array_keys($data) as $key) {
            if (! array_key_exists($key, $keys)) {
                Assert::fail("Unmapped key {$path}.{$key}");
            }
        }

        foreach ($keys as $key => $spec) {
            $value = $data[$key] ?? null;

            if ($value === null || ! is_array($value)) {
                continue;
            }

            $child = "{$path}.{$key}";

            if (isset($spec['list'])) {
                foreach ($value as $index => $item) {
                    self::assertMapped($item, $spec['list']::keys(), "{$child}[{$index}]");
                }
            } elseif (isset($spec['class'])) {
                self::assertMapped($value, $spec['class']::keys(), $child);
            } elseif (isset($spec['shape'])) {
                self::assertMapped($value, $spec['shape'], $child);
            }
        }
    }
}
