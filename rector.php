<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use SavinMikhail\AddNamedArgumentsRector\AddNamedArgumentsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/src',
        __DIR__ . '/database',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->cacheDirectory(__DIR__ . '/var/rector');

    $rectorConfig->skip([
        AddNamedArgumentsRector::class => [
            __DIR__ . '/src/Report/Application/Services/StartCsvExportAction.php',
        ],
    ]);

    $rectorConfig->rule(AddNamedArgumentsRector::class);
};