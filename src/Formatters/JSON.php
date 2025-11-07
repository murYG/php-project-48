<?php

namespace Differ\Formatters\JSON;

function format(array $diffData): string
{
    return json_encode($diffData);
}
