<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoController
{
    private $env;

    /**
     * @param $env
     */
    public function __construct(string $environment)
    {
        $this->env = $environment;
    }

    public function index(Request $request, string $foo)
    {
        return new Response(json_encode([
            'env' => $this->env,
            'ip' => $request->getClientIp(),
            'foo' => $foo,
        ]), 200, ['Content-Type' => 'application/json']);
    }
}
