<?php

namespace Pkboom\FileWatcher\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Pkboom\FileWatcher\FileWatcher;
use Symfony\Component\Finder\Finder;

class FileWatcherTest extends TestCase
{
    /** @test */
    public function itCanAcceptFilePath()
    {
        $watcher = FileWatcher::create(__DIR__.'/fixtures/example.php');

        $watcher->files->each(function ($timestamp, $file) {
            $this->assertEquals(__DIR__.'/fixtures/example.php', $file);
        });
    }

    /** @test */
    public function itCanAcceptAnArray()
    {
        $watcher = FileWatcher::create([
            __DIR__.'/fixtures/example.php',
            __DIR__.'/fixtures/example2.php',
        ]);

        $watcher->files->keys()->sort()->values()->pipe(function ($files) {
            $this->assertEquals(__DIR__.'/fixtures/example.php', $files[0]);
            $this->assertEquals(__DIR__.'/fixtures/example2.php', $files[1]);
        });
    }

    /** @test */
    public function itCanAcceptFinder()
    {
        $finder = (new Finder())
            ->in(__DIR__.'/fixtures')
            ->files();

        $watcher = FileWatcher::create($finder);

        $watcher->files->keys()->sort()->values()->pipe(function ($files) {
            $this->assertEquals(__DIR__.'/fixtures/example.php', $files[0]);
            $this->assertEquals(__DIR__.'/fixtures/example2.php', $files[1]);
        });
    }

    /** @test */
    public function itCanThrowAnExceptionWithAnInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        FileWatcher::create(null);
    }

    /** @test */
    public function itCanfind()
    {
        $watcher = FileWatcher::create(__DIR__.'/fixtures/example.php');

        $this->assertFalse($watcher->find()->hasChanges());

        file_put_contents(__DIR__.'/fixtures/example.php', 'changed');

        $this->assertTrue($watcher->find()->hasChanges());
    }

    /** @test */
    public function itCanRunACallbackIfAnyChange()
    {
        $finder = (new Finder())
            ->in(__DIR__.'/fixtures')
            ->files();

        $watcher = FileWatcher::create($finder);

        $proof = 'foo';

        $watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('foo', $proof);

        file_put_contents(__DIR__.'/fixtures/example2.php', 'changed');

        $watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('bar', $proof);
    }
}
