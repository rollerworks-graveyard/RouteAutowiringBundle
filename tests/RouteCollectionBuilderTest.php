<?php

declare(strict_types=1);

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests;

use PHPUnit\Framework\TestCase;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteCollectionBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @group legacy
 */
class RouteCollectionBuilderTest extends TestCase
{
    public function testImport()
    {
        $resolvedLoader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $resolver = $this->getMockBuilder(LoaderResolverInterface::class)->getMock();
        $resolver->expects($this->once())
            ->method('resolve')
            ->with('admin_routing.yml', 'yaml')
            ->willReturn($resolvedLoader);

        $originalRoute = new Route('/foo/path');
        $expectedCollection = new RouteCollection();
        $expectedCollection->add('one_test_route', $originalRoute);
        $expectedCollection->addResource(new FileResource(__DIR__.'/Fixtures/file_resource.yml'));

        $resolvedLoader
            ->expects($this->once())
            ->method('load')
            ->with('admin_routing.yml', 'yaml')
            ->willReturn($expectedCollection);

        $loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader->expects($this->any())
            ->method('getResolver')
            ->willReturn($resolver);

        // import the file!
        $routes = new RouteCollectionBuilder($loader);
        $importedRoutes = $routes->import('admin_routing.yml', '/', 'yaml');

        // get the collection back so we can look at it
        $addedCollection = $importedRoutes->build();
        $route = $addedCollection->get('one_test_route');
        $this->assertEquals($originalRoute, $route);
        // should return file_resource.yml, which is in the original collection
        $this->assertCount(1, $addedCollection->getResources());

        // make sure the routes were imported into the top-level builder
        $routeCollection = $routes->build();
        $this->assertCount(1, $routes->build());
        $this->assertCount(1, $routeCollection->getResources());
    }

    public function testImportAddResources()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder(new YamlFileLoader(new FileLocator([__DIR__.'/Fixtures/'])));
        $routeCollectionBuilder->import('file_resource.yml');
        $routeCollection = $routeCollectionBuilder->build();

        $this->assertCount(1, $routeCollection->getResources());
    }

    public function testImportWithoutLoaderThrowsException()
    {
        $this->expectException('BadMethodCallException');
        $collectionBuilder = new RouteCollectionBuilder();
        $collectionBuilder->import('routing.yml');
    }

    public function testAddsThePrefixOnlyOnceWhenLoadingMultipleCollections()
    {
        $firstCollection = new RouteCollection();
        $firstCollection->add('a', new Route('/a'));

        $secondCollection = new RouteCollection();
        $secondCollection->add('b', new Route('/b'));

        $loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader->expects($this->any())
            ->method('supports')
            ->willReturn(true);
        $loader
            ->expects($this->any())
            ->method('load')
            ->willReturn([$firstCollection, $secondCollection]);

        $routeCollectionBuilder = new RouteCollectionBuilder($loader);
        $routeCollectionBuilder->import('/directory/recurse/*', '/other/', 'glob');
        $routes = $routeCollectionBuilder->build()->all();

        $this->assertCount(2, $routes);
        $this->assertEquals('/other/a', $routes['a']->getPath());
        $this->assertEquals('/other/b', $routes['b']->getPath());
    }
}
