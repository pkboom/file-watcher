<?php

namespace Pkboom\FileWatcher\Test;

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
    public function itCanAcceptFinder()
    {
        $finder = (new Finder())
            ->in(__DIR__.'/fixtures')
            ->files();

        $watcher = FileWatcher::create($finder);

        $watcher->files->each(function ($timestamp, $file) {
            $this->assertEquals(__DIR__.'/fixtures/example.php', $file);
        });
    }

    /** @test */
    public function itCanFindChanges()
    {
        TestTime::freeze();

        $watcher = FileWatcher::create(__DIR__.'/fixtures/example.php');

        $this->assertFalse($watcher->findChanges()->hasChanges());

        TestTime::addMinute();

        file_put_contents(__DIR__.'/fixtures/example.php', 'haha');

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

        file_put_contents(__DIR__.'/fixtures/example.php', 'haha');

        $proof = 'foo';

        $watcher->findChanges()->runIfAny(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('bar', $proof);
    }
}
