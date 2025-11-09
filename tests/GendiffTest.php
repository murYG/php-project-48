<?php

namespace Hexlet\Phpunit\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GendiffTest extends TestCase
{
    #[DataProvider('genDiffDefaultProvider')]
    public function testGenDiffDefault(string $filePath1, string $filePath2, string $expected): void
    {
        $result = genDiff($this->getFixtureFullPath($filePath1), $this->getFixtureFullPath($filePath2));
        $this->assertStringEqualsFile($this->getFixtureFullPath($expected), $result);
    }

    #[DataProvider('genDiffFormatProvider')]
    public function testGenDiffFormat(string $filePath1, string $filePath2, string $format, string $expected): void
    {
        $file1 = $this->getFixtureFullPath($filePath1);
        $file2 = $this->getFixtureFullPath($filePath2);
        $result = genDiff($file1, $file2, $format);
        $this->assertStringEqualsFile($this->getFixtureFullPath($expected), $result);
    }

    public function testGenDiffFormatJson(): void
    {
        $file1 = $this->getFixtureFullPath('file1.tree.json');
        $file2 = $this->getFixtureFullPath('file2.tree.json');
        $result = genDiff($file1, $file2, "json");
        $this->assertJsonStringEqualsJsonFile($this->getFixtureFullPath('expected1.json.json'), $result);
    }

    public function testExceptionMessageExtNotSupported()
    {
        $this->expectExceptionMessage("*.html files not supported");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file1.html'));
    }

    public function testExceptionMessageFileNotFound()
    {
        $this->expectExceptionMessage("File " . $this->getFixtureFullPath('file22.json') . " not found");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file22.json'));
    }

    public function testExceptionMessageUnsupportedFormat()
    {
        $this->expectExceptionMessage("Unsupported format: html");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file2.tree.json'), "html");
    }

    public function testExceptionMessageParsingNotImplemented()
    {
        $this->expectExceptionMessage("Parsing TXT not implemented");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file1.txt'));
    }

    public function testExceptionMessageInvalidJson()
    {
        $this->expectExceptionMessage("Invalid JSON in " . $this->getFixtureFullPath('file5.json'));
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file5.json'));
    }

    public function testExceptionMessageInvalidYaml()
    {
        $this->expectExceptionMessage("Invalid YAML in " . $this->getFixtureFullPath('file4.yml'));
        genDiff($this->getFixtureFullPath('file4.yml'), $this->getFixtureFullPath('file4.yml'));
    }

    public function getFixtureFullPath(string $fixtureName): string
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public static function genDiffDefaultProvider(): array
    {
        return [
            "json & json" => [
                'file1.tree.json',
                'file2.tree.json',
                'expected1.stylish.txt'
            ],
            "json & json reverse" => [
                'file2.tree.json',
                'file1.tree.json',
                'expected2.stylish.txt'
            ],
            "json & json empty" => [
                'file4.json',
                'file4.json',
                "expected.empty.stylish.txt"
            ],
            "yaml & yaml" => [
                'file1.tree.yml',
                'file2.tree.yml',
                'expected1.stylish.txt'
            ],
            "json & yaml" => [
                'file1.tree.json',
                'file2.tree.yml',
                'expected1.stylish.txt'
            ]
        ];
    }

    public static function genDiffFormatProvider(): array
    {
        return [
            "stylish" => [
                'file1.tree.json',
                'file2.tree.json',
                'stylish',
                'expected1.stylish.txt'
            ],
            "plain" => [
                'file1.tree.json',
                'file2.tree.json',
                'plain',
                'expected1.plain.txt'
            ]
        ];
    }
}
