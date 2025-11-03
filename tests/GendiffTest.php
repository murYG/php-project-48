<?php

namespace Hexlet\Phpunit\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;

// Класс UtilsTest наследует класс TestCase
// Имя класса совпадает с именем файла
class GendiffTest extends TestCase
{
    // Метод (функция), определенный внутри класса,
    // Должен начинаться со слова test
    // Ключевое слово public нужно, чтобы PHPUnit мог вызвать этот тест снаружи
    public function testGenDiff(): void
    {
        $this->assertStringEqualsFile($this->getFixtureFullPath('expected1.txt'), genDiff($this->getFixtureFullPath('file1.json'), $this->getFixtureFullPath('file2.json')));
		$this->assertStringEqualsFile($this->getFixtureFullPath('expected2.txt'), genDiff($this->getFixtureFullPath('file3.json'), $this->getFixtureFullPath('file2.json')));
    }
	
	public function getFixtureFullPath(string $fixtureName): string
	{
		$parts = [__DIR__, 'fixtures', $fixtureName];
		return realpath(implode('/', $parts));
	}	
}
