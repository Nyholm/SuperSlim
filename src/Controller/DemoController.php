<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoController
{
    private $env;

    public function __construct(string $environment)
    {
        $this->env = $environment;
    }

    public function index(Request $request, string $foo): Response
    {
        return new JsonResponse(
            [
                'env' => $this->env,
                'ip' => $request->getClientIp(),
                'foo' => $foo,
            ]
        );
    }
}
