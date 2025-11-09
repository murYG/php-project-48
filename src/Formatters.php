<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\render as renderStylish;
use function Differ\Formatters\Plain\render as renderPlain;
use function Differ\Formatters\JSON\render as renderJson;

function format(string $format, array $diffData): string
{
    return match ($format) {
        'stylish' => renderStylish($diffData),
        'plain' => renderPlain($diffData),
        'json' => renderJson($diffData),
        default  => throw new \Exception("Unsupported format: $format")
    };
}

function toString(mixed $value, string $symbol = "'"): string
{
    return $value === null ? 'null' : trim(var_export($value, true), $symbol);
}
