<?php

namespace Pkboom\FileWatcher;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Symfony\Component\Finder\SplFileInfo;

class FileWatcher
{
    public $files;

    protected $changed = false;

    public function __construct($files)
    {
        $this->files = Collection::make($files)
            ->map(fn ($file) => $this->getPath($file))
            ->flatMap(fn ($file) => [$file => filemtime($file)]);
    }

    public function getPath($file)
    {
        if ($file instanceof SplFileInfo) {
            return $file->getRealPath();
        }

        if (is_file($file)) {
            return $file;
        }

        throw new InvalidArgumentException("Invalid file path: $file.");
    }

    public static function create($files)
    {
        return new static($files);
    }

    public function find()
    {
        clearstatcache();

        $this->files = $this->files->mapWithKeys(function ($timestamp, $file) {
            if ($timestamp !== filemtime($file)) {
                $this->changed = true;
            }

            return [$file => filemtime($file)];
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
}
