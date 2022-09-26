# Simple file watcher

Filewatcher is a simple file watcher. You can run a callback based on file changes.

```php
use Pkboom\FileWatcher\FileWatcher;
use React\EventLoop\Loop;

$watcher = FileWatcher::create((new Finder())->in('dir')->files());
// or
$watcher = FileWatcher::create('/path/to/file');

Loop::addPeriodicTimer(1, function () use ($watcher) {
    $watcher->find()->whenChanged(function () {
        //
    });
});
```

## Installation

```bash
composer require pkboom/file-watcher
```
