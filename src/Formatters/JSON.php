<?php

namespace Differ\Formatters\JSON;

function format(array $diffData): string
{
    $result = json_encode($diffData);
    if ($result === false) {
        throw new \Exception('Unexpected error');
    }

    return $result;
}
