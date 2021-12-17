<?php

namespace Pkboom\FileWatcher;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileWatcher
{
    public $files;

    public $changed = false;

    public function __construct($finder)
    {
        if (is_string($finder)) {
            $finder = [$finder];
        }

        if (is_array($finder)) {
            $this->files = Collection::make($finder)->flatMap(function ($file) {
                return [$file => filemtime($file)];
            });
        }

        if ($finder instanceof Finder) {
            $this->files = Collection::make($finder)->flatMap(function (SplFileInfo $file) {
                return [
                  $file->getRealPath() => filemtime($file->getRealPath()),
                ];
            });
        }

        if (!$this->files) {
            throw new InvalidArgumentException('Valid arguments: file path or Finder object.');
        }
    }

    public static function create($finder)
    {
        return new static($finder);
    }

    public function find()
    {
        clearstatcache();

        $this->files->each(function ($timestamp, $file) {
            if ($timestamp !== filemtime($file)) {
                $timestamp = filemtime($file);

                $this->changed = true;

                return false;
            }
        });

        return $this;
    }

    public function hasChanges()
    {
        return $this->changed;
    }

    public function whenChanged(callable $callback)
    {
        if ($this->hasChanges()) {
            return $callback();
        }
    }
}
