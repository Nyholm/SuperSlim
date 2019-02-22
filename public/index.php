<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

$kernel = new Kernel('dev', true);
$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();
