<?php

namespace Differ\Differ;

use function Funct\Collection\sortBy;
use function Funct\Collection\some;
use function Differ\Parsers\parse;
use function Differ\Formatters\format;

const SUPPORTED_TYPES = ['json', 'yml', 'yaml', 'txt'];

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $fileData1 = getFileData($pathToFile1);
    $fileData2 = getFileData($pathToFile2);

    $result1 = parse($fileData1);
    $result2 = parse($fileData2);

    $diffData = buildDiffTree($result1, $result2);
    return format($format, $diffData);
}

function getFileData(string $filePath): array
{
    if (!file_exists($filePath)) {
        throw new \Exception("File $filePath not found");
    }

    $pathInfo = pathinfo($filePath);
    $extension = $pathInfo['extension'] ?? '';
    if (!in_array($extension, SUPPORTED_TYPES, true)) {
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

function buildDiffTree(object $data1, object $data2): array
{
    $arr1 = get_object_vars($data1);
    $arr2 = get_object_vars($data2);

    $keys = array_values(sortBy(
        array_unique(array_merge(array_keys($arr1), array_keys($arr2))),
        fn ($value) => $value
    ));

    return array_map(function ($key) use ($data1, $data2) {
        if (!property_exists($data1, $key)) {
            return buildDiffDataElement($key, "added", $data2->$key);
        }

        if (!property_exists($data2, $key)) {
            return buildDiffDataElement($key, "deleted", $data1->$key);
        }

        if (is_object($data1->$key) && is_object($data2->$key)) {
            return buildDiffDataNode($key, buildDiffTree($data1->$key, $data2->$key));
        }

        if ($data1->$key === $data2->$key) {
            return buildDiffDataElement($key, "unchanged", $data1->$key);
        }

        return buildDiffDataElement($key, "changed", $data2->$key, $data1->$key);
    }, $keys);
}

function buildDiffDataNode(string $key, array $value): array
{
    return [
        'key' => $key,
        'children' => $value
    ];
}

function buildDiffDataElement(string $key, string $type, mixed $value, mixed $valuePrev = null): array
{
    return [
        'key' => $key,
        'type' => $type,
        'value' => buildDiffDataValue($value),
        'valuePrev' => buildDiffDataValue($valuePrev)
    ];
}

function buildDiffDataValue(mixed $value): mixed
{
    if (!is_object($value)) {
        return $value;
    }

    $keys = array_keys(get_object_vars($value));
    return array_map(
        fn ($item) => [
            'key' => $item,
            'value' => buildDiffDataValue($value->$item)
        ],
        $keys
    );
}
