<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

const SUPPORTED_EXTENSIONS = [
        'json' => 'JSON',
        'yml' => 'YAML',
        'yaml' => 'YAML'
    ];

function parse(string $filePath): array
{
    if (!file_exists($filePath)) {
        throw new \Exception("File $filePath not found");
    }

    $pathInfo = pathinfo($filePath);
    $fileType = SUPPORTED_EXTENSIONS[$pathInfo['extension']] ?? '';
    if ($fileType === '') {
        throw new \Exception("*.{$pathInfo['extension']} files not supported");
    }

    $fileContents = file_get_contents($filePath);

    $func = __NAMESPACE__ . "\\parse$fileType";
    if (!function_exists($func)) {
        throw new \Exception("Unsupported file type: $fileType");
    }

    $data = $func($fileContents);
    if ($data === null) {
        throw new \Exception("Invalid $fileType in $filePath");
    }

    return ['fileType' => $fileType, 'data' => objectToArray($data)];
}

function parseJSON(string $fileContents): object | null
{
    $data = json_decode($fileContents);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $data;
}

function parseYAML(string $fileContents): object | null
{
    $data = Yaml::parse($fileContents, Yaml::PARSE_OBJECT_FOR_MAP);
    if (!is_object($data)) {
        return null;
    }

    return $data;
}

function objectToArray(object $data): array
{
    $arr = get_object_vars($data);
    return array_map(fn ($item) => is_object($item) ? objectToArray($item) : $item, $arr);
}

function getFileType(array $parseResult): string
{
    return $parseResult['fileType'];
}

function getData(array $parseResult): array
{
    return $parseResult['data'];
}
