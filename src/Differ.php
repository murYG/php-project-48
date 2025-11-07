<?php

namespace Differ\Differ;

use Funct;

use function Differ\Parsers\parse;
use function Differ\Parsers\getFileType;
use function Differ\Parsers\getData;
use function Differ\Formatters\format;

function genDiff(string $pathToFile1, string $pathToFile2, $format = 'stylish'): string
{
    $result1 = parse($pathToFile1);
    $result2 = parse($pathToFile2);

    $data1 = getData($result1);
    $data2 = getData($result2);

    $diffData = array_values(genDiffData($data1, $data2));
    return format($format, $diffData);
}

function genDiffData(array $data1, array $data2): array
{
    $keys = Funct\Collection\sortBy(
        array_unique(array_merge(array_keys($data1), array_keys($data2))),
        fn ($value) => $value
    );

    return array_map(function ($key) use ($data1, $data2) {
        if (!array_key_exists($key, $data1)) {
            return genDiffDataElement($key, 1, $data2[$key]);
        } elseif (!array_key_exists($key, $data2)) {
            return genDiffDataElement($key, -1, $data1[$key]);
        } else {
            $value1 = $data1[$key];
            $value2 = $data2[$key];

            if (isAssoc($value1) && isAssoc($value2)) {
                return genDiffDataNode($key, genDiffData($value1, $value2));
            } elseif ($data1[$key] === $data2[$key]) {
                return genDiffDataElement($key, 0, $data1[$key]);
            } else {
                return genDiffDataElement($key, 2, $data2[$key], $data1[$key]);
            }
        }
    }, $keys);
}

function genDiffDataNode(string $key, array $value): array
{
    return ['key' => $key, 'type' => 'node', 'children' => array_values($value)];
}

function genDiffDataElement(string $key, int $action, $value, $valuePrev = null): array
{
    return [
        'key' => $key,
        'type' => 'element',
        'action' => $action,
        'value' => genDiffDataValue($value),
        'valuePrev' => genDiffDataValue($valuePrev)
    ];
}

function genDiffDataValue($value)
{
    if (!is_array($value)) {
        return $value;
    }

    $keys = array_keys($value);
    return array_map(
        fn ($item) => ['key' => $item, 'type' => 'value', 'value' => genDiffDataValue($value[$item])],
        $keys
    );
}

function isNode($element): bool
{
    return is_array($element) && array_key_exists("type", $element) && $element['type'] === 'node';
}

function isElement($element): bool
{
    return is_array($element) && array_key_exists("type", $element) && $element['type'] === 'element';
}

function isAssoc($arr): bool
{
    if (!is_array($arr) || count($arr) === 0) {
        return false;
    }

    $keys = array_keys($arr);
    return Funct\Collection\some($keys, fn ($item) => !is_int($item));
}
