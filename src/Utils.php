<?php

declare(strict_types=1);

namespace Sober\Controller;

class Utils
{
    public static function isFilePhp(string $filename): bool
    {
        return pathinfo($filename, PATHINFO_EXTENSION) === 'php';
    }

    public static function doesFileContain(string $filename, string $str): bool
    {
        $contents = @file_get_contents($filename);
        return $contents !== false && str_contains($contents, $str);
    }

    public static function isArrayIndexed(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    public static function doesStringContainMarkup(string $str): bool
    {
        return $str !== strip_tags($str);
    }

    public static function convertToSnakeCase(string $str): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }

    public static function convertToKebabCase(string $str): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $str));
    }

    public static function convertKebabCaseToSnakeCase(string $str): string
    {
        return strtolower(str_replace('-', '_', $str));
    }
}
