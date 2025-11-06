<?php

namespace Differ\Formatters\Plain;

use function Differ\Formatters\toString;
use function Differ\Differ\isElement;

function format(array $diffData): string
{
    if (count($diffData) === 0) {
        return "";
    }

    return implode("\n", stringifyDiff($diffData, 1));
}

function stringifyDiff($value, int $depth, $key = ''): array
{
    return array_reduce($value, function ($acc, $item) use ($depth, $key) {
        if (isElement($item)) {
            $str = formatElement($item, $key);
            if ($str !== '') {
                $acc[] = $str;
            }
            return $acc;
        }

        $result = $key . formatNode($item, $depth);

        $children = $item['children'];
        return [...$acc, ...stringifyDiff($children, $depth + 1, $result)];
    }, []);
}

function formatNode(array $diffNode): string
{
    return "{$diffNode['key']}.";
}

function formatElement(array $diffElement, $key): string
{
    $keyStr = $key . $diffElement['key'];
    $valueStr = formatValue($diffElement['value']);
    $valuePrevStr = formatValue($diffElement['valuePrev']);

    return getActionView($diffElement['action'], $keyStr, $valueStr, $valuePrevStr);
}

function formatValue($value): string
{
    if (is_array($value)) {
        return '[complex value]';
    } else {
        return toString($value, " \n\r\t\v\x00");
    }
}

function getActionView($action, $key, $value1, $value2)
{
    $arAction = [
        1 => "Property '$key' was added with value: $value1",
        -1 => "Property '$key' was removed",
        2 => "Property '$key' was updated. From $value2 to $value1"
    ];

    return $arAction[$action] ?? '';
}
