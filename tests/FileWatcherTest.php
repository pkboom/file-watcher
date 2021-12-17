<?php

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Pkboom\FileWatcher\FileWatcher;
use Symfony\Component\Finder\Finder;

/**
 * @see \Pkboom\FileWatcher\FileWatcher
 */
class FileWatcherTest extends TestCase
{
    /** @test */
    public function it_accepts_a_file_path()
    {
        $watcher = FileWatcher::create(__DIR__.'/temp/example.php');

        $watcher->files->each(function ($timestamp, $file) {
            $this->assertEquals(__DIR__.'/temp/example.php', $file);
        });
    }

    /** @test */
    public function it_accepts_an_array_of_file_paths()
    {
        $watcher = FileWatcher::create([
            __DIR__.'/temp/example.php',
            __DIR__.'/temp/example2.php',
        ]);

        $watcher->files->keys()->sort()->values()->pipe(function ($files) {
            $this->assertEquals(__DIR__.'/temp/example.php', $files[0]);
            $this->assertEquals(__DIR__.'/temp/example2.php', $files[1]);
        });
    }

    /** @test */
    public function it_accepts_a_finder()
    {
        $finder = (new Finder())
            ->in(__DIR__.'/temp')
            ->files();

        $watcher = FileWatcher::create($finder);

        $watcher->files->keys()->sort()->values()->pipe(function ($files) {
            $this->assertEquals(__DIR__.'/temp/example.php', $files[0]);
            $this->assertEquals(__DIR__.'/temp/example2.php', $files[1]);
        });
    }

    /** @test */
    public function it_throws_an_exception_with_a_non_existing_file()
    {
        $this->expectException(InvalidArgumentException::class);

        FileWatcher::create('non_existing_file');
    }

    /** @test */
    public function it_can_find_changes()
    {
        $watcher = FileWatcher::create(__DIR__.'/temp/example.php');

        $timestamp = $watcher->files->first();

        $this->assertFalse($watcher->find()->exists());

        usleep(700000);

        file_put_contents(__DIR__.'/temp/example.php', 'changed');

        $this->assertTrue($watcher->find()->exists());

        $updatedAt = $watcher->files->first();

        $this->assertLaterThan($timestamp, $updatedAt);
    }

    /** @test */
    public function it_runs_a_callback_if_any_change()
    {
        $finder = (new Finder())
            ->in(__DIR__.'/temp')
            ->files();

        $watcher = FileWatcher::create($finder);

        $proof = 'foo';

        $watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('foo', $proof);

        usleep(700000);

        file_put_contents(__DIR__.'/temp/example2.php', 'changed');

        $watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('bar', $proof);
    }

    /** @test */
    public function it_set_changed_to_false_after_a_callback_runs()
    {
        $watcher = FileWatcher::create([__DIR__.'/temp/example.php']);

        $this->assertFalse($watcher->exists());

        usleep(700000);

        file_put_contents(__DIR__.'/temp/example.php', 'changed');

        $watcher->find();

        $this->assertTrue($watcher->exists());

        $watcher->whenChanged(function () {});

        $this->assertFalse($watcher->exists());
    }

    public function assertLaterThan($before, $after)
    {
        $this->assertTrue($before < $after);
    }
}
