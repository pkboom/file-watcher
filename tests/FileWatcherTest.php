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
    /** @test */
    public function it_accepts_a_finder()
    {
        $finder = (new Finder())->in($this->tempDir())->files();

        $this->watcher = FileWatcher::create($finder);

        $this->assertInstanceOf(Finder::class, $this->watcher->finder);
    }

    /** @test */
    public function it_accepts_a_path()
    {
        $path = $this->tempDir('example');

        $this->watcher = FileWatcher::create($path);

        $this->assertIsArray($this->watcher->finder);
    }

    /**
     * @dataProvider provider
     * @test
     */
    public function it_can_find_changes($finder)
    {
        $watch = $this->watcher($finder);

        $this->assertFalse($watch->find()->exists());

        $timestamp = $watch->updates[$this->tempDir('example')];

        sleep(1);

        file_put_contents($this->tempDir('example'), microtime());

        $updatedAt = $watch->updates[$this->tempDir('example')];

        $this->assertTrue($watch->find()->exists());

        $updatedAt = $watch->updates[$this->tempDir('example')];

        $this->assertLaterThan($timestamp, $updatedAt);
    }

    /**
     * @dataProvider provider
     * @test
     */
    public function it_runs_a_callback_if_any_change($finder)
    {
        $watcher = $this->watcher($finder);

        $proof = 'foo';

        $watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('foo', $proof);

        sleep(1);

        file_put_contents($this->tempDir('example'), microtime());

        $watcher->find()->whenChanged(function () use (&$proof) {
            $proof = 'bar';
        });

        $this->assertEquals('bar', $proof);
    }

    /**
     * @dataProvider provider
     * @test
     */
    public function it_set_changed_to_false_after_a_callback_runs($finder)
    {
        $watcher = $this->watcher($finder);

        $this->assertFalse($watcher->exists());

        sleep(1);

        file_put_contents($this->tempDir('example'), microtime());

        $watcher->find();

        $this->assertTrue($watcher->exists());

        $watcher->whenChanged(function () {});

        $this->assertFalse($watcher->exists());
    }

    /** @test */
    public function it_can_detect_a_new_file()
    {
        $finder = (new Finder())->in($this->tempDir())->files();

        $watcher = $this->watcher($finder);

        $newFile = $this->tempDir('new_file');

        $this->assertFalse(isset($watcher->updates[$newFile]));

        file_put_contents($newFile, microtime());

        $watcher->find();

        $this->assertTrue(isset($watcher->updates[$newFile]));

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function assertLaterThan($before, $after)
    {
        $this->assertTrue($before < $after);
    }

    public function watcher($finder)
    {
        return FileWatcher::create($finder);
    }

    public function provider()
    {
        return [
            [(new Finder())->in(__DIR__.'/temp')->files()],
            [__DIR__.'/temp/example'],
        ];
    }

    public function tempDir($filename = null)
    {
        return __DIR__.'/temp'.($filename ? "/$filename" : null);
    }
}
