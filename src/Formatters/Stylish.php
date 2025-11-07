<?php

namespace Differ\Formatters\Stylish;

use function Differ\Formatters\toString;
use function Differ\Differ\isElement;

const REPLACER = ' ';
const REPLACER_COUNT = 4;
const LEFT_SHIFT = "  ";
const LEFT_SHIFT_LENGTH = 2;

function format(array $diffData): string
{
    if (count($diffData) === 0) {
        return "{}";
    }

    return "{\n" . implode("\n", stringifyDiff($diffData, 1)) . "\n}";
}

function stringifyDiff($value, int $depth): array
{
    return array_reduce($value, function ($acc, $item) use ($depth) {
        if (isElement($item)) {
            return [...$acc, formatElement($item, $depth)];
        }

        $result = formatNode($item, $depth) . " {";

        $prefixPrevious = getPrefix($depth);

        $children = $item['children'];
        return [...$acc, $result, ...stringifyDiff($children, $depth + 1), "$prefixPrevious}"];
    }, []);
}

function formatNode(array $diffNode, int $depth): string
{
    return getPrefix($depth, LEFT_SHIFT_LENGTH, LEFT_SHIFT) . "{$diffNode['key']}:";
}

function formatElement(array $diffElement, int $depth): string
{
    $prefix = getPrefix($depth, LEFT_SHIFT_LENGTH);

    $keyStr = $diffElement['key'];
    $valueStr = formatValue($diffElement['value'], $depth + 1);
    $valuePrevStr = formatValue($diffElement['valuePrev'], $depth + 1);

    return getActionView($diffElement['action'], $keyStr, $valueStr, $valuePrevStr, $prefix);
}

function formatValue($value, int $depth): string
{
    if (!is_array($value)) {
        return toString($value);
    }

    $prefixCurrent = getPrefix($depth);
    $prefixPrevious = getPrefix($depth - 1);

    return "{\n" . array_reduce($value, function ($acc, $item) use ($depth, $prefixCurrent) {
        return $acc . "$prefixCurrent{$item['key']}: " . formatValue($item['value'], $depth + 1) . "\n";
    }, "") . "$prefixPrevious}";
}

function getPrefix(int $depth, int $shiftLen = 0, string $shift = ''): string
{
    return str_repeat(REPLACER, $depth * REPLACER_COUNT - $shiftLen) . $shift;
}

function getActionView($action, $key, $value1, $value2, $prefix)
{
    $arAction = [
        1 => "$prefix+ $key: $value1",
        0 => "$prefix  $key: $value1",
        -1 => "$prefix- $key: $value1",
        2 => "$prefix- $key: $value2\n$prefix+ $key: $value1"
    ];

    return $arAction[$action] ?? '';
}
