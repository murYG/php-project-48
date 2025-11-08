<?php

namespace Differ\Differ;

use Funct;

use function Differ\Parsers\parse;
use function Differ\Formatters\format;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $result1 = parse($pathToFile1);
    $result2 = parse($pathToFile2);

    $diffData = array_values(genDiffData($result1, $result2));
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

function genDiffDataElement(string $key, int $action, mixed $value, mixed $valuePrev = null): array
{
    return [
        'key' => $key,
        'type' => 'element',
        'action' => $action,
        'value' => genDiffDataValue($value),
        'valuePrev' => genDiffDataValue($valuePrev)
    ];
}

function genDiffDataValue(mixed $value): mixed
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

function isNode(array $element): bool
{
    return array_key_exists("type", $element) && $element['type'] === 'node';
}

function isElement(array $element): bool
{
    return array_key_exists("type", $element) && $element['type'] === 'element';
}

function isComplexValue(array $element): bool
{
    return array_key_exists("type", $element) && $element['type'] === 'value';
}

function isAssoc(mixed $arr): bool
{
    if (!is_array($arr) || count($arr) === 0) {
        return false;
    }

    $keys = array_keys($arr);
    return Funct\Collection\some($keys, fn ($item) => !is_int($item));
}

function getKey(array $diffNodeElement): string
{
    return $diffNodeElement["key"];
}

function getChildren(array $diffNode): array
{
    if (!isNode($diffNode)) {
        return [];
    }

    return $diffNode["children"];
}

function getAction(array $diffElement): int | null
{
    if (!isElement($diffElement)) {
        return null;
    }

    return $diffElement["action"];
}

function getValue(array $diffElement, bool $previous = false): mixed
{
    if (!isElement($diffElement) && !isComplexValue($diffElement)) {
        return null;
    }

    if ($previous === true) {
        return isElement($diffElement) ? $diffElement["valuePrev"] : null;
    }

    return $diffElement["value"];
}
