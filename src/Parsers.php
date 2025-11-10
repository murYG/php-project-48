<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

const SUPPORTED_TYPES = [
        'json' => 'JSON',
        'yml' => 'YAML',
        'yaml' => 'YAML',
        'txt' => 'TXT'
    ];

function parse(array $fileData): object
{
    $fileType = SUPPORTED_TYPES[$fileData['extension']] ?? '';
    return match ($fileType) {
        'JSON' => parseJSON($fileData['contents']),
        'YAML' => parseYAML($fileData['contents']),
        default  => throw new \Exception("Parsing $fileType not implemented")
    };
}

function parseJSON(string $fileContents): object
{
    return json_decode($fileContents, false, 512, JSON_THROW_ON_ERROR);
}

function parseYAML(string $fileContents): object
{
    return Yaml::parse($fileContents, Yaml::PARSE_OBJECT_FOR_MAP);
}
