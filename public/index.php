<?php

declare(strict_types=1);

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

/** @var \Illuminate\Foundation\Application $kernel */
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);