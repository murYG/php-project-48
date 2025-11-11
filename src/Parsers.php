<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(array $fileData): object
{
    return match ($fileData['extension']) {
        'json' => parseJSON($fileData['contents']),
        'yml', 'yaml' => parseYAML($fileData['contents']),
        default  => throw new \Exception("Parsing {$fileData['extension']} not implemented")
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
