<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $filePath): array
{
    if (!file_exists($filePath)) {
        throw new \Exception("File $filePath not found");
    }

    $pathInfo = pathinfo($filePath);
    $format = supportedFormat()[$pathInfo['extension']] ?? '';
    if ($format === '') {
        throw new \Exception("*.{$pathInfo['extension']} files not supported");
    }

    $fileContents = file_get_contents($filePath);

    return ['format' => $format, 'data' => formatData($format, $filePath, $fileContents)];
}

function supportedFormat()
{
    return [
        'json' => 'JSON',
        'yml' => 'YAML',
        'yaml' => 'YAML'
    ];
}

function formatData($format, $filePath, $fileContents)
{
    $func = [
        'JSON' => function ($format, $filePath, $fileContents) {
            $data = json_decode($fileContents);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid $format in $filePath");
            }

            return get_object_vars($data);
        },
        'YAML' => function ($format, $filePath, $fileContents) {
            $data = Yaml::parse($fileContents);
            if (!is_array($data)) {
                throw new \Exception("Invalid $format in $filePath");
            }
            return $data;
        }
    ];

    return call_user_func($func[$format], $format, $filePath, $fileContents);
}

function getFormat($parseResult)
{
    return $parseResult['format'];
}

function getData($parseResult)
{
    return $parseResult['data'];
}
