<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoController
{
    public function index($foo, Request $request)
    {
        return new Response('demo: '.$foo);
    }
}
