<?php

namespace Hexlet\Phpunit\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class UnitTest extends TestCase
{
    public function testExtensionNotSupported()
    {
        $this->expectExceptionMessage("*.html files not supported");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file1.html'));
    }

    public function testFileNotFound()
    {
        $this->expectExceptionMessage("File " . $this->getFixtureFullPath('file22.json') . " not found");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file22.json'));
    }

    public function testUnsupportedFormat()
    {
        $this->expectExceptionMessage("Unsupported format: html");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file2.tree.json'), "html");
    }

    public function testParsingNotImplemented()
    {
        $this->expectExceptionMessage("Parsing TXT not implemented");
        genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file1.txt'));
    }

    public function getFixtureFullPath(string $fixtureName): string
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }
}
