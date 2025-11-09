<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

use function Differ\Differ\getFileExtension;
use function Differ\Differ\getFileContents;
use function Differ\Differ\getFilePath;

const SUPPORTED_TYPES = [
        'json' => 'JSON',
        'yml' => 'YAML',
        'yaml' => 'YAML',
        'txt' => 'TXT'
    ];

function parse(array $fileData): object
{
    $fileType = SUPPORTED_TYPES[getFileExtension($fileData)] ?? '';
    return match ($fileType) {
        'JSON' => parseJSON($fileData),
        'YAML' => parseYAML($fileData),
        default  => throw new \Exception("Parsing $fileType not implemented")
    };
}

function parseJSON(array $fileData): object
{
    $result = json_decode(getFileContents($fileData));
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("Invalid JSON in " . getFilePath($fileData));
    }

    return $result;
}

function parseYAML(array $fileData): object
{
    $result = Yaml::parse(getFileContents($fileData), Yaml::PARSE_OBJECT_FOR_MAP);
    if (!is_object($result)) {
        throw new \Exception("Invalid YAML in " . getFilePath($fileData));
    }

    return $result;
}
