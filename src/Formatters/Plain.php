<?php

namespace Differ\Formatters\Plain;

use function Differ\Formatters\toString;

function render(array $diffData): string
{
    if (empty($diffData)) {
        return "";
    }

    return implode("\n", stringifyDiff($diffData, 1));
}

function stringifyDiff(array $value, int $depth, string $key = ''): array
{
    return array_reduce($value, function ($acc, $item) use ($depth, $key) {
        if (array_key_exists("type", $item)) {
            $str = formatElement($item, $key);
            if ($str !== '') {
                return [...$acc, $str];
            } else {
                return $acc;
            }
        }

        $result = $key . formatNode($item);
        return [...$acc, ...stringifyDiff($item["children"], $depth + 1, $result)];
    }, []);
}

function formatNode(array $diffNode): string
{
    return $diffNode["key"] . ".";
}

function formatElement(array $diffElement, string $nodeKey): string
{
    $prefix = $nodeKey . $diffElement["key"];

    $valueStr = formatValue($diffElement["value"]);
    $valuePrevStr = formatValue($diffElement["valuePrev"]);

    return getView($diffElement["type"], $prefix, $valueStr, $valuePrevStr);
}

function formatValue(mixed $value): string
{
    if (is_array($value)) {
        return '[complex value]';
    } else {
        return toString($value, " \n\r\t\v\x00");
    }
}

function getView(string $action, string $key, string $value1, string $value2): string
{
    $arAction = [
        "added" => "Property '$key' was added with value: $value1",
        "deleted" => "Property '$key' was removed",
        "changed" => "Property '$key' was updated. From $value2 to $value1"
    ];

    return $arAction[$action] ?? '';
}
