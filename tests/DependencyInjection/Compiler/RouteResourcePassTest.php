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
use Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\Compiler\RouteResourcePass;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteSlotLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RouteResourcePassTest extends AbstractCompilerPassTestCase
{
    /**
     * @before
     */
    public function registerRouteLoader()
    {
        $this->container->register('rollerworks_route_autowiring.route_loader', RouteSlotLoader::class)
            ->setArguments([new Reference('service_container'), [], []]);
    }

    /**
     * @test
     */
    public function it_registers_the_route_resources()
    {
        $routeImporter = new RouteImporter($this->container);
        $routeImporter->addObjectResource($this);
        $routeImporter->import('first.yml', 'main');
        $routeImporter->import('second.yml', 'main');
        $this->compile();

        $loaderDef = $this->container->findDefinition('rollerworks_route_autowiring.route_loader');
        $resources = $loaderDef->getArgument(2);

        // Resources are provided as service References, but id cannot be predicted.
        // So instead loop trough each and analyze the actual referenced service definition.
        $expectedFilename = (new \ReflectionClass($this))->getFileName();
        $found = false;

        /** @var Definition[] $resourceServices */
        $resourceServices = [];

        foreach ($resources as $serviceId) {
            $resourceServices[(string) $serviceId] = $this->container->findDefinition((string) $serviceId);
        }

        // assertContains() doesn't work because of to much factors
        // we only care for the class and argument.
        foreach ($resourceServices as $resourceService) {
            if ($resourceService->getClass() !== FileResource::class) {
                continue;
            }

            if ($expectedFilename === $resourceService->getArgument(0)) {
                $found = true;

                break;
            }
        }

        if (!$found) {
            $this->fail("Expected resource '$expectedFilename' in the resource list.");
        }
    }

    /**
     * @test
     */
    public function it_does_not_register_the_route_resources_when_debugging_is_disabled()
    {
        $routeImporter = new RouteImporter($this->container);
        $routeImporter->addObjectResource($this);
        $routeImporter->import('first.yml', 'main');
        $routeImporter->import('second.yml', 'main');

        $this->setParameter('kernel.debug', false);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('rollerworks_route_autowiring.route_loader', 2, []);
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RouteResourcePass());
        $container->setParameter('kernel.debug', true);
    }
}
