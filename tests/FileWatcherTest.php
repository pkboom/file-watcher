<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Pkboom\FileWatcher\FileWatcher;
use Symfony\Component\Finder\Finder;

/**
 * @see \Pkboom\FileWatcher\FileWatcher
 */
class FileWatcherTest extends TestCase
{
    public function setUp(): void
    {
        $finder = (new Finder())
            ->in(__DIR__.'/temp')
            ->files();

        $this->watcher = FileWatcher::create($finder);
    }

    /** @test */
    public function it_accepts_a_finder()
    {
        $this->assertInstanceOf(Finder::class, $this->watcher->finder);
    }

    /** @test */
    public function it_can_find_changes()
    {
        $this->assertFalse($this->watcher->find()->exists());

        $timestamp = $this->watcher->updates[__DIR__.'/temp/example.php'];

        usleep(700000);

        file_put_contents(__DIR__.'/temp/example.php', 'changed');

        $this->assertTrue($this->watcher->find()->exists());

        $updatedAt = $this->watcher->updates[__DIR__.'/temp/example.php'];

        $this->assertLaterThan($timestamp, $updatedAt);
    }

    /** @test */
    public function it_runs_a_callback_if_any_change()
    {
        $proof = 'foo';

        $this->watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('foo', $proof);

        usleep(700000);

        file_put_contents(__DIR__.'/temp/example2.php', 'changed');

        $this->watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('bar', $proof);
    }

    /** @test */
    public function it_set_changed_to_false_after_a_callback_runs()
    {
        $this->assertFalse($this->watcher->exists());

        usleep(700000);

        file_put_contents(__DIR__.'/temp/example.php', 'changed');

        $this->watcher->find();

        $this->assertTrue($this->watcher->exists());

        $this->watcher->whenChanged(function () {});

        $this->assertFalse($this->watcher->exists());
    }

    /** @test */
    public function it_can_detect_a_new_file()
    {
        $newFile = __DIR__.'/temp/new_file.php';

        $this->assertFalse(isset($this->watcher->updates[$newFile]));

        file_put_contents($newFile, 'new');

        $this->watcher->find();

        $this->assertTrue(isset($this->watcher->updates[$newFile]));

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function assertLaterThan($before, $after)
    {
        $this->assertTrue($before < $after);
    }
}
