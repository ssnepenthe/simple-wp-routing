<?php

declare(strict_types=1);

namespace ToyWpRouting;

// @todo Generate rewrite hash?
// @todo Get hash from rewrite?
// @todo Get global matched rewrite hash?
class Support
{
    public static function applyPrefix(string $value, string $prefix): string
    {
        if ('' === $prefix) {
            return $value;
        }

        if ($prefix === substr($value, 0, strlen($prefix))) {
            return $value;
        }

        return "{$prefix}{$value}";
    }

    public static function applyPrefixToKeys(array $array, string $prefix): array
    {
        if ('' === $prefix) {
            return $array;
        }

        $newArray = [];

        foreach ($array as $key => $value) {
            $newArray[static::applyPrefix($key, $prefix)] = $value;
        }

        return $newArray;
    }

    public static function buildQuery(array $queryArray): string
    {
        if (empty($queryArray)) {
            return '';
        }

        return 'index.php?' . implode('&', array_map(
            fn ($key, $value) => "{$key}={$value}",
            array_keys($queryArray),
            $queryArray
        ));
    }

    public static function isValidMethodsList(array $methods): bool
    {
        if ([] === $methods) {
            return false;
        }

        return empty(array_diff(
            $methods,
            ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        ));
    }

    public static function parseQuery(string $query): array
    {
        if ('index.php?' === substr($query, 0, 10)) {
            $query = substr($query, 10);
        }

        parse_str($query, $result);

        return $result;
    }
}
