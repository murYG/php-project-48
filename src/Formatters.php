<?php

namespace Differ\Formatters;

function format(string $format, array $diffData): string
{
    $func = __NAMESPACE__ . "\\$format\\format";
    if (!function_exists($func)) {
        throw new \Exception("Unsupported format: $format");
    }

    return $func($diffData);
}

function toString(mixed $value, string $symbol = "'"): string
{
    return $value === null ? 'null' : trim(var_export($value, true), $symbol);
}
