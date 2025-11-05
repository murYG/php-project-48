<?php

namespace Differ\Formatters\Stylish;

use function Differ\Formatters\toString;
use function Differ\Differ\isElement;

const REPLACER = ' ';
const REPLACER_COUNT = 4;
const LEFT_SHIFT = "  ";
const LEFT_SHIFT_COUNT = 2;
const ACTION_VIEW = [1 => "+", 0 => " ", -1 => "-"];

function format($diffData): string
{
    if (count($diffData) === 0) {
        return "{}";
    }

    return "{\n" . array_reduce($diffData, function ($acc, $item) {
        return $acc . stringifyDiff($item, 1) . "\n";
    }, '') . "}";
}

function stringifyDiff($value, $depth): string
{
    $prefixCurrent = str_repeat(REPLACER, $depth * REPLACER_COUNT - LEFT_SHIFT_COUNT);

    if (isElement($value)) {
        return $prefixCurrent . formatElement($value, $depth);
    }

    $result = $prefixCurrent . LEFT_SHIFT . formatNode($value) . " {\n";

    $prefixPrevious = str_repeat(REPLACER, $depth * REPLACER_COUNT);

    $children = $value['children'];
    return $result . array_reduce($children, function ($acc, $item) use ($depth) {
        return $acc . stringifyDiff($item, $depth + 1) . "\n";
    }, "") . "$prefixPrevious}";
}

function formatNode($diffNode)
{
    return "{$diffNode['key']}:";
}

function formatElement($diffElement, $depth)
{
    $keyStr = $diffElement['key'];
    $actionStr = ACTION_VIEW[$diffElement['action']];
    $valueStr = formatValue($diffElement['value'], $depth + 1);

    return "$actionStr $keyStr: $valueStr";
}

function formatValue($value, $depth): string
{
    if (!is_array($value)) {
        return toString($value);
    }

    $prefixCurrent = str_repeat(REPLACER, $depth * REPLACER_COUNT);
    $prefixPrevious = str_repeat(REPLACER, ($depth - 1) * REPLACER_COUNT);

    return "{\n" . array_reduce($value, function ($acc, $item) use ($depth, $prefixCurrent) {
        return $acc . "$prefixCurrent{$item['key']}: " . formatValue($item['value'], $depth + 1) . "\n";
    }, "") . "$prefixPrevious}";
}
