<?php

namespace Hexlet\Phpunit\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;

class GendiffTest extends TestCase
{
    public function testGenDiff(): void
    {
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected1.tree.txt'), genDiff($this->getFixtureFullPath('file1.tree.json'), $this->getFixtureFullPath('file2.tree.json')));
		$this->assertStringEqualsFile($this->getFixtureFullPath('expected2.tree.txt'), genDiff($this->getFixtureFullPath('file2.tree.json'), $this->getFixtureFullPath('file1.tree.json')));
		$this->assertEquals("{}", genDiff($this->getFixtureFullPath('file4.json'), $this->getFixtureFullPath('file4.json')));
		
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected1.tree.txt'), genDiff($this->getFixtureFullPath('file1.tree.yml'), $this->getFixtureFullPath('file2.tree.yml')));
		$this->assertStringEqualsFile($this->getFixtureFullPath('expected2.tree.txt'), genDiff($this->getFixtureFullPath('file2.tree.yml'), $this->getFixtureFullPath('file1.tree.yml')));
    }
	
	public function testExceptionMessage1()
    {
		$this->expectExceptionMessage("*.txt files not supported");
		genDiff($this->getFixtureFullPath('file1.json'), $this->getFixtureFullPath('file1.txt'));
    }
	
	//public function testExceptionMessage2()
    //{
	//	$this->expectExceptionMessage("Different file types unsupported");
	//	genDiff($this->getFixtureFullPath('file1.json'), $this->getFixtureFullPath('file2.yml'));
    //}
	
	//public function testExceptionMessage3()
    //{
	//	$this->expectExceptionMessage("Different file types unsupported");
	//	genDiff($this->getFixtureFullPath('file1.yml'), $this->getFixtureFullPath('file2.json'));
    //}
	
	public function testExceptionMessage4()
    {
		$this->expectExceptionMessage("Invalid JSON in " . $this->getFixtureFullPath('file5.json'));
		genDiff($this->getFixtureFullPath('file1.json'), $this->getFixtureFullPath('file5.json'));
    }
	
	public function testExceptionMessage5()
    {
		$this->expectExceptionMessage("Invalid YAML in " . $this->getFixtureFullPath('file4.yml'));
		genDiff($this->getFixtureFullPath('file1.yml'), $this->getFixtureFullPath('file4.yml'));
    }
	
	public function getFixtureFullPath(string $fixtureName): string
	{
		$parts = [__DIR__, 'fixtures', $fixtureName];
		return realpath(implode('/', $parts));
	}	
}
