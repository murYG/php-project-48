<?php

namespace Gendiff\Gendiff;

function parse(string $filePath)
{
    $file = file_get_contents($filePath);

    $result = json_decode($file);

    return $result;
}

function printData(\stdClass $data, $format)
{
    echo "Формат: $format\n";
    print_r($data);
}
