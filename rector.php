<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/src',
        __DIR__ . '/database',
        __DIR__ . '/tests',
    ]);

    // Кеш в папку var
    $rectorConfig->cacheDirectory(__DIR__ . '/var/rector');

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84,
    ]);
};