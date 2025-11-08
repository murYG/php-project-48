<?php

namespace Hexlet\Phpunit\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GendiffTest extends TestCase
{
    public function testGenDiff(): void
    {
        $result1 = genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file2.tree.json'));
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected1.stylish.txt'), $result1);

        $result2 = genDiff($this->getFixtureFullPath('file2.tree.json'), $this->getFixtureFullPath('file1.tree.json'));
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected2.stylish.txt'), $result2);

        $result3 = genDiff($this->getFixtureFullPath('file4.json'), $this->getFixtureFullPath('file4.json'));
        $this->assertEquals("{}", $result3);

        $result4 = genDiff($this->getFixtureFullPath('file1.tree.yml'), $this->getFixtureFullPath('file2.tree.yml'));
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected1.stylish.txt'), $result4);

        $result5 = genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file2.tree.yml'));
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected1.stylish.txt'), $result5);

        $file1 = $this->getFixtureFullPath('file1.tree.json');
        $file2 = $this->getFixtureFullPath('file2.tree.json');
        $result6 = genDiff($file1, $file2, "plain");
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected1.plain.txt'), $result6);

        $file1 = $this->getFixtureFullPath('file1.tree.json');
        $file2 = $this->getFixtureFullPath('file2.tree.json');
        $result7 = genDiff($file1, $file2, "json");
        $this->assertJsonStringEqualsJsonFile($this->getFixtureFullPath('expected2.json.json'), $result7);
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
        $this->expectExceptionMessage("Parsing *.txt files not implemented");
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
}
