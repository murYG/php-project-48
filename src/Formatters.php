<?php

namespace Differ\Formatters;

function format($format, $diffData): string
{
    $func = __NAMESPACE__ . "\\$format\\format";
    if (!function_exists($func)) {
        throw new \Exception("Unsupported format: $format");
    }

    return $func($diffData);
}

function toString($value)
{
    return trim(var_export($value, true), "'");
}
