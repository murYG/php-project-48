<?php

namespace Differ\Formatters\Plain;

use function Differ\Formatters\toString;
use function Differ\Differ\isElement;
use function Differ\Differ\getKey;
use function Differ\Differ\getChildren;
use function Differ\Differ\getAction;
use function Differ\Differ\getValue;

function format(array $diffData): string
{
    if (count($diffData) === 0) {
        return "";
    }

    return implode("\n", stringifyDiff($diffData, 1));
}

function stringifyDiff(array $value, int $depth, string $key = ''): array
{
    return array_reduce($value, function ($acc, $item) use ($depth, $key) {
        if (isElement($item)) {
            $str = formatElement($item, $key);
            if ($str !== '') {
                return [...$acc, $str];
            } else {
                return $acc;
            }
        }

        $result = $key . formatNode($item);

        $children = getChildren($item);
        return [...$acc, ...stringifyDiff($children, $depth + 1, $result)];
    }, []);
}

function formatNode(array $diffNode): string
{
    return getKey($diffNode) . ".";
}

function formatElement(array $diffElement, string $nodeKey): string
{
    $prefix = $nodeKey . getKey($diffElement);

    $valueStr = formatValue(getValue($diffElement));
    $valuePrevStr = formatValue(getValue($diffElement, true));

    return getActionView(getAction($diffElement), $prefix, $valueStr, $valuePrevStr);
}

function formatValue(mixed $value): string
{
    if (is_array($value)) {
        return '[complex value]';
    } else {
        return toString($value, " \n\r\t\v\x00");
    }
}

function getActionView(int $action, string $key, string $value1, string $value2): string
{
    $arAction = [
        1 => "Property '$key' was added with value: $value1",
        -1 => "Property '$key' was removed",
        2 => "Property '$key' was updated. From $value2 to $value1"
    ];

    return $arAction[$action] ?? '';
}
