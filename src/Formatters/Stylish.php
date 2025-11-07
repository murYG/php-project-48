<?php

namespace Differ\Formatters\Stylish;

use function Differ\Formatters\toString;
use function Differ\Differ\isElement;
use function Differ\Differ\getKey;
use function Differ\Differ\getChildren;
use function Differ\Differ\getAction;
use function Differ\Differ\getValue;

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

        $children = getChildren($item);
        return [...$acc, $result, ...stringifyDiff($children, $depth + 1), "$prefixPrevious}"];
    }, []);
}

function formatNode(array $diffNode, int $depth): string
{
    return getPrefix($depth, LEFT_SHIFT_LENGTH, LEFT_SHIFT) . getKey($diffNode) . ":";
}

function formatElement(array $diffElement, int $depth): string
{
    $prefix = getPrefix($depth, LEFT_SHIFT_LENGTH);

    $valueStr = formatValue(getValue($diffElement), $depth + 1);
    $valuePrevStr = formatValue(getValue($diffElement, true), $depth + 1);

    return getActionView(getAction($diffElement), getKey($diffElement), $valueStr, $valuePrevStr, $prefix);
}

function formatValue($value, int $depth): string
{
    if (!is_array($value)) {
        return toString($value);
    }

    $prefixCurrent = getPrefix($depth);
    $prefixPrevious = getPrefix($depth - 1);

    return "{\n" . array_reduce($value, function ($acc, $item) use ($depth, $prefixCurrent) {
        $itemKey = getKey($item);
        return $acc . "$prefixCurrent$itemKey: " . formatValue(getValue($item), $depth + 1) . "\n";
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
