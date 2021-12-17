<?php

namespace Pkboom\FileWatcher\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Pkboom\FileWatcher\FileWatcher;
use Spatie\TestTime\TestTime;
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
    public function itCanFindChanges()
    {
        TestTime::freeze();

        $watcher = FileWatcher::create(__DIR__.'/fixtures/example.php');

        $this->assertFalse($watcher->findChanges()->hasChanges());

        TestTime::addMinute();

        file_put_contents(__DIR__.'/fixtures/example.php', 'foo');

        $this->assertTrue($watcher->findChanges()->hasChanges());
    }

    /** @test */
    public function itCanRunACallbackIfAnyChange()
    {
        TestTime::freeze();

        $finder = (new Finder())
            ->in(__DIR__.'/fixtures')
            ->files();

        $watcher = FileWatcher::create($finder);

        TestTime::addMinute();

        file_put_contents(__DIR__.'/fixtures/example2.php', 'foo');

        $proof = 'foo';

        $watcher->findChanges()->runIfAny(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('bar', $proof);
    }
}
