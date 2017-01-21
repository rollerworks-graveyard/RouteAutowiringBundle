<?php

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests;

use Rollerworks\Bundle\RouteAutowiringBundle\RouteSlotLoader;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class RouteSlotLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_supports_only_autowired_routes()
    {
        $loader = new RouteSlotLoader($this->getContainer(), []);

        self::assertTrue($loader->supports('main', 'rollerworks_autowiring'));
        self::assertFalse($loader->supports('main', 'yaml'));
    }

    /** @test */
    public function it_loads_a_route_collection_from_the_container()
    {
        $aR = new RouteCollectionBuilder();
        $aR->add('main/', 'MainController', 'main');

        $bR = new RouteCollectionBuilder();
        $bR->add('backend/', 'BackendController', 'backend_main');

        $container = $this->prophesize(ContainerInterface::class);
        $container->get($a = 'rollerworks_route_autowiring.routing_slot.frontend')->willReturn($aR);
        $container->get($b = 'rollerworks_route_autowiring.routing_slot.backend')->willReturn($bR);

        $loader = new RouteSlotLoader($container->reveal(), ['frontend' => $a, 'backend' => $b]);

        self::assertInstanceOf(RouteCollection::class, $routeCollection = $loader->load('frontend'));
        self::assertArrayHasKey('main', $routeCollection->getIterator());
        self::assertArrayNotHasKey('backend_main', $routeCollection->getIterator());

        self::assertInstanceOf(RouteCollection::class, $routeCollection = $loader->load('backend'));
        self::assertArrayHasKey('backend_main', $routeCollection->getIterator());
        self::assertArrayNotHasKey('main', $routeCollection->getIterator());

        // While not existing it should not fail.
        self::assertInstanceOf(RouteCollection::class, $routeCollection = $loader->load('unknown'));
        self::assertEmpty($routeCollection->getIterator());
    }

    /** @test */
    public function it_registers_resources_on_all_collections()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('main/', 'MainController', 'main');

        $container = $this->prophesize(ContainerInterface::class);
        $container->get($a = 'rollerworks_route_autowiring.routing_slot.frontend')->willReturn($routeCollectionBuilder);

        $loader = new RouteSlotLoader(
            $container->reveal(), ['frontend' => $a],
            [$r = $this->createResourceStub(), $r2 = $this->createResourceStub('stub2')]
        );

        self::assertInstanceOf(RouteCollection::class, $routeCollection = $loader->load('frontend'));
        self::assertArrayHasKey('main', $routeCollection->getIterator());

        $resources = $routeCollection->getResources();
        self::assertContains($r, $resources);
        self::assertContains($r2, $resources);

        // Even when the slot is not registered the resources should still be registered
        // as they can be enabled in the future.
        self::assertInstanceOf(RouteCollection::class, $routeCollection = $loader->load('backend'));
        self::assertArrayNotHasKey('main', $routeCollection->getIterator());

        $resources = $routeCollection->getResources();
        self::assertContains($r, $resources);
        self::assertContains($r2, $resources);
    }

    private function createResourceStub($name = 'stub')
    {
        $resource = $this->getMockBuilder(ResourceInterface::class)->getMock();
        $resource->expects(self::any())->method('__toString')->willReturn($name);

        return $resource;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private function getContainer()
    {
        return $this->getMockBuilder(ContainerInterface::class)->getMock();
    }
}
