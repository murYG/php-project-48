<?php

namespace Differ\Differ;

use Funct;

use function Differ\Parsers\parse;
use function Differ\Parsers\getFormat;
use function Differ\Parsers\getData;

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $result1 = parse($pathToFile1);
    $result2 = parse($pathToFile2);

    if (getFormat($result1) !== getFormat($result2)) {
        throw new \Exception('Different formats unsupported');
    }

    $data1 = getData($result1);
    $data2 = getData($result2);

    $keys = Funct\Collection\sortBy(
        array_unique(array_merge(array_keys($data1), array_keys($data2))),
        fn ($value) => $value
    );

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
        return "{}";
    }

    return "{\n" . implode("\n", $arResult) . "\n}";
}

function getDiffFormatted($key, $value, $sign)
{
    $value = var_export($value, true);
    return "  $sign $key: $value";
}
