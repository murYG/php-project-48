<?php

namespace Differ\Formatters\JSON;

function render(array $diffData): string
{
    return json_encode($diffData, JSON_THROW_ON_ERROR);
}
