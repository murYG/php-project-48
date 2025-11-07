<?php

namespace Differ\Formatters\JSON;

function format(array $diffData): string
{
    if (count($diffData) === 0) {
        return json_encode([]);
    }

    return json_encode($diffData);
}
