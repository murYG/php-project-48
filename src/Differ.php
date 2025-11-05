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

    //if (getFileType($result1) !== getFileType($result2)) {
    //    throw new \Exception('Different file types unsupported');
    //}

    $data1 = getData($result1);
    $data2 = getData($result2);

    $diffData = genDiffData($data1, $data2);
    return format($format, $diffData);
}

function genDiffData(array $data1, array $data2): array
{
    $keys = Funct\Collection\sortBy(
        array_unique(array_merge(array_keys($data1), array_keys($data2))),
        fn ($value) => $value
    );

    return array_reduce($keys, function ($acc, $key) use ($data1, $data2) {
        if (!array_key_exists($key, $data1)) {
            $acc[] = genDiffDataElement($key, $data2[$key], 1);
        } elseif (!array_key_exists($key, $data2)) {
            $acc[] = genDiffDataElement($key, $data1[$key], -1);
        } else {
            $value1 = $data1[$key];
            $value2 = $data2[$key];

            if (isAssoc($value1) && isAssoc($value2)) {
                $acc[] = genDiffDataNode($key, genDiffData($value1, $value2));
            } elseif ($data1[$key] === $data2[$key]) {
                $acc[] = genDiffDataElement($key, $data1[$key], 0);
            } else {
                $acc[] = genDiffDataElement($key, $data1[$key], -1);
                $acc[] = genDiffDataElement($key, $data2[$key], 1);
            }
        }

        return $acc;
    }, []);
}

function genDiffDataNode(string $key, array $value): array
{
    return ['key' => $key, 'type' => 'node', 'children' => $value];
}

function genDiffDataElement(string $key, $value, int $action): array
{
    return ['key' => $key, 'type' => 'element', 'action' => $action, 'value' => genDiffDataValue($value)];
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
