<?php

namespace Pkboom\FileWatcher;

use Symfony\Component\Finder\Finder;

class FileWatcher
{
    public $finder;

    protected $changed = false;

    public $updates;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;

        $this->updates = $this->storeUpdateTime();
    }

    public static function create($files)
    {
        return new static($files);
    }

    public function find()
    {
        clearstatcache();

        $this->finder = collect($this->finder)->each(function ($file) {
            if (isset($this->updates[$file->getRealPath()]) &&
                $this->updates[$file->getRealPath()] !== filemtime($file->getRealPath())) {
                $this->changed = true;
            }

            $this->updates[$file->getRealPath()] = filemtime($file->getRealPath());
        });

        return $this;
    }

    public function exists()
    {
        return $this->changed;
    }

    public function whenChanged(callable $callback)
    {
        if ($this->exists()) {
            $this->changed = false;

            $callback();
        }
    }

    public function storeUpdateTime()
    {
        return collect($this->finder)->mapWithKeys(function ($file) {
            return [$file->getRealPath() => filemtime($file->getRealPath())];
        });
    }
}
