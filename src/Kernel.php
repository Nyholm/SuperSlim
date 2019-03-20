<?php

declare(strict_types=1);

namespace App;

use App\Middleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The heart of our application. We configure the container and then start running the middleware.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Kernel
{
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

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

        return $this->container->get(Runner::class)->handle($request);
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $containerDumpFile = $this->getProjectDir().'/var/cache/'.$this->env.'/container.php';
        if ($this->debug || !file_exists($containerDumpFile)) {
            $container = new ContainerBuilder();
            $container->setParameter('kernel.project_dir', $this->getProjectDir());
            $container->setParameter('kernel.cache_dir', $this->getProjectDir().'/var/cache/'.$this->env);
            $container->setParameter('kernel.environment', $this->env);
            $container->setParameter('kernel.debug', $this->debug);

            $container->registerForAutoconfiguration(MiddlewareInterface::class)
                ->addTag('kernel.middleware');

            $loader = $this->getContainerLoader($container, $this->getProjectDir().'/config');
            $loader->load('{packages}/*'.self::CONFIG_EXTS, 'glob');
            $loader->load('{packages}/'.$this->env.'/**/*'.self::CONFIG_EXTS, 'glob');
            $loader->load('{services}'.self::CONFIG_EXTS, 'glob');
            $loader->load('{services}_'.$this->env.self::CONFIG_EXTS, 'glob');

            $container->compile();

            //dump the container
            @mkdir(dirname($containerDumpFile), 0777, true);
            file_put_contents(
                $containerDumpFile,
                (new PhpDumper($container))->dump(['class' => 'CachedContainer'])
            );
        }

        require_once $containerDumpFile;
        $this->container = new \CachedContainer();
        $this->booted = true;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    private function getContainerLoader(ContainerBuilder $container, string $configDir)
    {
        $locator = new FileLocator($configDir);
        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);

        return new DelegatingLoader($resolver);
    }

    private function getProjectDir()
    {
        return dirname(__DIR__);
    }
}
