<?php

namespace Pkboom\FileWatcher;

class File
{
    public $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

      public function getRealPath()
      {
          return $this->path;
      }
}
