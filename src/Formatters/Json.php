<?php

namespace Differ\Formatters\Json;

function render(array $diffData): string
{
    return json_encode($diffData, JSON_THROW_ON_ERROR);
}
