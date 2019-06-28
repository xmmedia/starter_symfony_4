<?php

declare(strict_types=1);

namespace App;

use App\EventSourcing\Aggregate\AggregateRepository;
use App\EventSourcing\Aggregate\AggregateTranslator;
use App\Infrastructure\Repository as EventSourceRepository;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log';
    }

    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }

    public function process(ContainerBuilder $container)
    {
        $this->loadEventSourceRepositories($container);
    }

    private function loadEventSourceRepositories(ContainerBuilder $container): void
    {
        $repositories = [
            [
                'repository_class' => EventSourceRepository\UserRepository::class,
                'aggregate_type'   => Model\User\User::class,
                'stream_name'      => 'user',
            ],
            [
                'repository_class' => EventSourceRepository\AuthRepository::class,
                'aggregate_type'   => Model\Auth\Auth::class,
                'stream_name'      => 'auth',
            ],
            [
                'repository_class' => EventSourceRepository\EnquiryRepository::class,
                'aggregate_type'   => Model\Enquiry\Enquiry::class,
                'stream_name'      => 'enquiry',
            ],
        ];

        foreach ($repositories as $repository) {
            $container->setDefinition(
                $repository['repository_class'],
                new ChildDefinition(AggregateRepository::class)
                )
                ->setArguments([
                    $repository['repository_class'],
                    new Reference('prooph_event_store.default'),
                    $repository['aggregate_type'],
                    new Reference(AggregateTranslator::class),
                    // "_event_stream" will be appended to this
                    // see \App\EventStore\PersistenceStrategy\StreamStrategy::generateTableName()
                    $repository['stream_name'],
                ])
            ;
        }
    }
}
