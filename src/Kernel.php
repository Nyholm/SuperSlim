<?php

declare(strict_types=1);

namespace App;

use App\Middleware\ExceptionHandler;
use App\Middleware\RouterFor50PlusRoutes;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Kernel
{
    private $booted = false;
    private $debug;
    private $env;

    /** @var Container */
    private $container;

    public function __construct(string $env, bool $debug = false)
    {
        $this->debug = $debug;
        $this->env = $env;
    }

    /**
     * Handle a Request and turn it in to a response.
     */
    public function handle(Request $request): Response
    {
        $this->boot();

        $middleware[] = $this->container->get(ExceptionHandler::class);
        $router = $this->container->get(RouterFor50PlusRoutes::class);
        $router->setContainer($this->container);
        $middleware[] = $router;
        $middleware[] = new \App\Middleware\Router($this->container);

        return (new Runner($middleware))->handle($request);
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $containerDumpFile = $this->getProjectDir().'/var/cache/'.$this->env.'/container.php';
        if (!$this->debug && file_exists($containerDumpFile)) {
            require_once $containerDumpFile;
            $container = new \CachedContainer();
        } else {
            $container = new ContainerBuilder();
            $container->setParameter('kernel.project_dir', $this->getProjectDir());
            $container->setParameter('kernel.environment', $this->env);

            $fileLocator = new FileLocator($this->getProjectDir().'/config');
            $loader = new YamlFileLoader($container, $fileLocator);
            try {
                $loader->load('services.yaml');
                $loader->load('services_'.$this->env.'.yaml');
            } catch (FileLocatorFileNotFoundException $e) {
            }

            $container->compile();

            //dump the container
            @mkdir(dirname($containerDumpFile), 0777, true);
            file_put_contents(
                $containerDumpFile,
                (new PhpDumper($container))->dump(['class' => 'CachedContainer'])
            );
        }

        $this->container = $container;

        $this->booted = true;
    }

    private function getProjectDir()
    {
        return dirname(__DIR__);
    }
}
