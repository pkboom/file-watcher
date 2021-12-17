# Simple file watcher

Filewatcher is a simple file watcher. You can run a callback based on file changes.

```php
use Pkboom\FileWatcher\FileWatcher;
use React\EventLoop\Loop;

$watcher = FileWatcher::create('/path/to/example.php');

// or

$watcher = FileWatcher::create((new Finder())->in('dir')->files());

Loop::addPeriodicTimer(1, function () use ($watcher) {
    $watcher->findChanges()->runIfAny(function () {
        //
    });
});
```

Real documentation is in the works, but for now read the tests.

## Installation

```bash
composer require pkboom/file-watcher
```
