<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoController
{
    public function index(Request $request, $foo)
    {
        return new Response('demo: '.$foo);
    }
}
