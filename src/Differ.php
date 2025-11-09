<?php

namespace Differ\Differ;

use function Funct\Collection\sortBy;
use function Funct\Collection\some;
use function Differ\Parsers\parse;
use function Differ\Formatters\format;

const SUPPORTED_EXTENSIONS = [
    'json',
    'yml',
    'yaml',
    'txt'
];

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $fileData1 = getFileData($pathToFile1);
    $fileData2 = getFileData($pathToFile2);

    $result1 = parse($fileData1);
    $result2 = parse($fileData2);

    $diffData = genDiffData($result1, $result2);
    return format($format, $diffData);
}

function getFileData(string $filePath): array
{
    if (!file_exists($filePath)) {
        throw new \Exception("File $filePath not found");
    }

    $pathInfo = pathinfo($filePath);
    $extension = $pathInfo['extension'] ?? '';
    if (!in_array($extension, SUPPORTED_EXTENSIONS, true)) {
        throw new \Exception("*.$extension files not supported");
    }

    $fileContents = file_get_contents($filePath);
    if ($fileContents === false) {
        throw new \Exception('Unexpected error');
    }

    return [
        'extension' => $extension,
        'contents' => $fileContents,
        'path' => $filePath
    ];
}

function getFileExtension(array $fileData): string
{
    return $fileData['extension'];
}

function getFileContents(array $fileData): string
{
    return $fileData['contents'];
}

function getFilePath(array $fileData): string
{
    return $fileData['path'];
}

function genDiffData(object $data1, object $data2): array
{
    $arr1 = objectToArray($data1);
    $arr2 = objectToArray($data2);

    return genDiffDataRec($arr1, $arr2);
}

function genDiffDataRec(array $data1, array $data2): array
{
    $keys = array_values(sortBy(
        array_unique(array_merge(array_keys($data1), array_keys($data2))),
        fn ($value) => $value
    ));

    return array_map(function ($key) use ($data1, $data2) {
        if (!array_key_exists($key, $data1)) {
            return genDiffDataElement($key, "added", $data2[$key]);
        } elseif (!array_key_exists($key, $data2)) {
            return genDiffDataElement($key, "deleted", $data1[$key]);
        } elseif (isAssoc($data1[$key]) && isAssoc($data2[$key])) {
            return genDiffDataNode($key, genDiffDataRec($data1[$key], $data2[$key]));
        } elseif ($data1[$key] === $data2[$key]) {
            return genDiffDataElement($key, "unchanged", $data1[$key]);
        } else {
            return genDiffDataElement($key, "changed", $data2[$key], $data1[$key]);
        }
    }, $keys);
}

function objectToArray(object $data): array
{
    $arr = get_object_vars($data);
    return array_map(fn ($item) => is_object($item) ? objectToArray($item) : $item, $arr);
}

function genDiffDataNode(string $key, array $value): array
{
    return [
        'key' => $key,
        'children' => $value
    ];
}

function genDiffDataElement(string $key, string $type, mixed $value, mixed $valuePrev = null): array
{
    return [
        'key' => $key,
        'type' => $type,
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
        fn ($item) => [
            'key' => $item,
            'value' => genDiffDataValue($value[$item])
        ],
        $keys
    );
}

function isNode(array $element): bool
{
    return array_key_exists("children", $element);
}

function isElement(array $element): bool
{
    return array_key_exists("type", $element);
}

function isAssoc(mixed $arr): bool
{
    if (!is_array($arr) || count($arr) === 0) {
        return false;
    }

    $keys = array_keys($arr);
    return some($keys, fn ($item) => !is_int($item));
}

function getKey(array $diffNodeElement): string
{
    if (!array_key_exists("key", $diffNodeElement)) {
        throw new \Exception("Incorrect structure");
    }

    return $diffNodeElement["key"];
}

function getChildren(array $diffNode): array
{
    if (!isNode($diffNode)) {
        return [];
    }

    return $diffNode["children"];
}

function getAction(array $diffElement): string
{
    if (!isElement($diffElement)) {
        throw new \Exception("Incorrect structure");
    }

    return $diffElement["type"];
}

function getValue(array $diffElement, bool $previous = false): mixed
{
    if ($previous === true) {
        return isElement($diffElement) ? $diffElement["valuePrev"] : null;
    }

    return $diffElement["value"];
}
