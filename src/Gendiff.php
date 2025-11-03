<?php

namespace Differ\Differ;

use Funct;

function parse(string $filePath)
{
    $file = file_get_contents($filePath);
    $result = json_decode($file);

    return get_object_vars($result);
}

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $result1 = parse($pathToFile1);
    $result2 = parse($pathToFile2);

    $keys = array_unique(array_merge(array_keys($result1), array_keys($result2)));
    $keys = Funct\Collection\sortBy($keys, fn ($value) => $value);

    $arResult = array_reduce($keys, function ($acc, $key) use ($result1, $result2) {
        if (!isset($result1[$key])) {
            $acc[] = getDiffFormatted($key, $result2[$key], "+");
        } elseif (!isset($result2[$key])) {
            $acc[] = getDiffFormatted($key, $result1[$key], "-");
        }

        if (isset($result1[$key]) && isset($result2[$key])) {
            if ($result1[$key] === $result2[$key]) {
                $acc[] = getDiffFormatted($key, $result1[$key], " ");
            } else {
                $acc[] = getDiffFormatted($key, $result1[$key], "-");
                $acc[] = getDiffFormatted($key, $result2[$key], "+");
            }
        }

        return $acc;
    }, []);

    return "{\n" . implode("\n", $arResult) . "\n}\n";
}
function getDiffFormatted($key, $value, $sign)
{
    $value = var_export($value, true);
    return "  $sign $key: $value";
}
