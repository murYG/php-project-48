<?php

namespace Hexlet\Phpunit\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GendiffTest extends TestCase
{
    #[DataProvider('genDiffProvider')]
    public function testGenDiff(string $filePath1, string $filePath2): void
    {
        $file1 = $this->getFixtureFullPath($filePath1);
        $file2 = $this->getFixtureFullPath($filePath2);

        $expected = $this->getFixtureFullPath('expected.default.txt');
        $result = genDiff($file1, $file2);
        $this->assertStringEqualsFile($expected, $result);

        $expected = $this->getFixtureFullPath('expected.stylish.txt');
        $result = genDiff($file1, $file2, "stylish");
        $this->assertStringEqualsFile($expected, $result);

        $expected = $this->getFixtureFullPath('expected.plain.txt');
        $result = genDiff($file1, $file2, "plain");
        $this->assertStringEqualsFile($expected, $result);

        $expected = $this->getFixtureFullPath('expected1.json.json');
        $result = genDiff($file1, $file2, "json");
        $this->assertJsonStringEqualsJsonFile($expected, $result);
    }

    public function getFixtureFullPath(string $fixtureName): string
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public static function genDiffProvider(): array
    {
        return [
            "json files" => [
                'file1.tree.json',
                'file2.tree.json'
            ],
            "yaml files" => [
                'file1.tree.yml',
                'file2.tree.yml'
            ]
        ];
    }
}
