<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\Compiler\RouteAutowiringPass;
use Rollerworks\Bundle\RouteAutowiringBundle\ResourceLoader;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteSlotLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class RouteAutowiringPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->container->register('routing.resolver', LoaderResolver::class)
            ->setArguments(
                [[new Reference('routing.loader'), new Reference('rollerworks_route_autowiring.route_loader')]]
            )
            ->setPublic(false);

        $this->container->register('rollerworks_route_autowiring.route_loader', RouteSlotLoader::class)
            ->setArguments([new Reference('service_container'), [], []]);

        $this->container->register('rollerworks_route_autowiring.resource_loader', ResourceLoader::class)
            ->setArguments([new Reference('routing.resolver')])
            ->setPublic(false);

        $this->container->register('file_locator', FileLocator::class)
            ->setArguments([dirname(__DIR__).'/../Fixtures/']);

        $this->container->register('routing.loader', YamlFileLoader::class)
            ->setArguments([new Reference('file_locator')]);
    }

    /**
     * @test
     */
    public function it_registers_from_collections_for_a_slot()
    {
        $routeImporter = new RouteImporter($this->container);
        $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/first.yml', 'frontend');
        $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/second.yml', 'frontend');

        $this->compile();

        $serviceId = 'rollerworks_route_autowiring.routing_slot.frontend';

        $this->assertContainerBuilderHasService($serviceId, RouteCollectionBuilder::class);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, 'import', ['@AcmeSomethingBundle/Resources/config/routing/first.yml', '/', null]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, 'import', ['@AcmeSomethingBundle/Resources/config/routing/second.yml', '/', null]);

        $this->assertLoaderHasSlots(['frontend']);
    }

    /**
     * @test
     */
    public function it_registers_from_collections_for_slots()
    {
        $routeImporter = new RouteImporter($this->container);
        $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/frontend.yml', 'frontend');
        $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/backend.yml', 'backend');

        $this->compile();

        $serviceId = 'rollerworks_route_autowiring.routing_slot.frontend';
        $this->assertContainerBuilderHasService($serviceId, RouteCollectionBuilder::class);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, 'import', ['@AcmeSomethingBundle/Resources/config/routing/frontend.yml', '/', null]);

        $serviceId = 'rollerworks_route_autowiring.routing_slot.backend';
        $this->assertContainerBuilderHasService($serviceId, RouteCollectionBuilder::class);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, 'import', ['@AcmeSomethingBundle/Resources/config/routing/backend.yml', '/', null]);

        $this->assertLoaderHasSlots(['frontend', 'backend']);
    }

    /**
     * @test
     */
    public function it_resolves_slot_name()
    {
        $this->container->setParameter('slot_name', 'main');

        $routeImporter = new RouteImporter($this->container);
        $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/first.yml', '%slot_name%');
        $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/second.yml', 'main');

        $this->compile();

        $serviceId = 'rollerworks_route_autowiring.routing_slot.main';

        $this->assertContainerBuilderHasService($serviceId, RouteCollectionBuilder::class);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, 'import', ['@AcmeSomethingBundle/Resources/config/routing/first.yml', '/', null]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, 'import', ['@AcmeSomethingBundle/Resources/config/routing/second.yml', '/', null]);

        $this->assertLoaderHasSlots(['main']);
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RouteAutowiringPass());
        $container->setParameter('kernel.debug', true);
    }

    private function assertLoaderHasSlots(array $slots)
    {
        $paramValue = [];

        foreach ($slots as $slot) {
            $paramValue[$slot] = 'rollerworks_route_autowiring.routing_slot.'.$slot;
        }

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('rollerworks_route_autowiring.route_loader', 1, $paramValue);
    }
}
