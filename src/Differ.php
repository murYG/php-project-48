<?php

namespace Differ\Differ;

use Funct;

function parse(string $filePath): array
{
    $fileContents = file_get_contents($filePath);
    $jsonData = json_decode($fileContents);

    return get_object_vars($jsonData);
}

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $data1 = parse($pathToFile1);
    $data2 = parse($pathToFile2);

    $keys = array_unique(array_merge(array_keys($data1), array_keys($data2)));
    $keys = Funct\Collection\sortBy($keys, fn ($value) => $value);

    $arResult = array_reduce($keys, function ($acc, $key) use ($data1, $data2) {
        if (!isset($data1[$key])) {
            $acc[] = getDiffFormatted($key, $data2[$key], "+");
        } elseif (!isset($data2[$key])) {
            $acc[] = getDiffFormatted($key, $data1[$key], "-");
        } elseif ($data1[$key] === $data2[$key]) {
            $acc[] = getDiffFormatted($key, $data1[$key], " ");
        } else {
            $acc[] = getDiffFormatted($key, $data1[$key], "-");
            $acc[] = getDiffFormatted($key, $data2[$key], "+");
        }

        return $acc;
    }, []);

    if (count($arResult) === 0) {
        return "";
    }

    return "{\n" . implode("\n", $arResult) . "\n}";
}

function getDiffFormatted($key, $value, $sign)
{
    $value = var_export($value, true);
    return "  $sign $key: $value";
}
