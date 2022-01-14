<?php

namespace App\Tests\Unit\App\Util;

use App\Util\FilePathUtil;
use PHPUnit\Framework\TestCase;

class FilePathUtilTest extends TestCase
{
    public function testItProperlySplitPath(): void
    {
        $path = 'images/project/projectId/image name.png';

        $split = FilePathUtil::splitPath($path);

        $this->assertEquals('images', $split[0]);
        $this->assertEquals('project', $split[1]);
        $this->assertEquals('projectId', $split[2]);
        $this->assertEquals('image name.png', $split[3]);
    }

    public function testGetParentDirectory(): void
    {
        $this->assertEquals('images/project/projectId', FilePathUtil::getParentDirectory('images/project/projectId/image name.png'));
        $this->assertEquals('', FilePathUtil::getParentDirectory('images'));
    }

    public function testGetFileName(): void
    {
        $this->assertEquals('test.png', FilePathUtil::getFileName('images/project/test.png'));
    }


    public function testStartsWith(): void
    {
        $this->assertTrue(FilePathUtil::startsWith('./test', '.'));
        $this->assertTrue(FilePathUtil::startsWith('/test', ''));
        $this->assertTrue(FilePathUtil::startsWith('Test', 'T'));
        $this->assertTrue(FilePathUtil::startsWith('0/test', '0'));

        $this->assertFalse(FilePathUtil::startsWith('test', 'T'));
    }

    public function testEndsWith(): void
    {
        $this->assertTrue(FilePathUtil::endsWith('test.', '.'));
        $this->assertTrue(FilePathUtil::endsWith('test/', '/'));
        $this->assertTrue(FilePathUtil::endsWith('test0', '0'));
        $this->assertTrue(FilePathUtil::endsWith('test\\', '\\'));
    }
}
