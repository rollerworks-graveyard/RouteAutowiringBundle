<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests\Functional;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RouteLoaderTest extends FunctionalTestCase
{
    /**
     * @dataProvider provideExpectedImportedRoutes
     */
    public function testRoutesToImportedRoutes($uri, $expectedRoute)
    {
        $client = self::newClient();

        $client->request('GET', $uri);
        $this->assertEquals('Route: '.$expectedRoute, $client->getResponse()->getContent());
    }

    public function testFailsWithNonImportedRoutes()
    {
        $client = self::newClient();

        try {
            $client->request('GET', '/backend/products/list');
            $response = $client->getInternalResponse();

            self::assertEquals(404, $response->getStatusCode());
            self::assertStringContainsString('No route found for &quot;GET /backend/products/list&quot;', $response->getContent());
        } catch (NotFoundHttpException $e) {
            self::assertStringContainsString('No route found for "GET /backend/products/list"', $e->getMessage());
        }
    }

    public function testRoutesToAutowiredRouteWhenEnabled()
    {
        $client = self::newClient(['config' => 'with_backend.yml']);

        $client->request('GET', '/backend/products/list');
        $this->assertEquals('Route: backend_products_list', $client->getResponse()->getContent());

        $client->request('GET', '/backend/products/show/50');
        $this->assertEquals('Route: backend_products_show', $client->getResponse()->getContent());
    }

    public function provideExpectedImportedRoutes()
    {
        return [
            'frontend_products_show' => ['/products/show', 'frontend_products_show'],
            'frontend_products_search' => ['/products/search', 'frontend_products_search'],

            // Second import
            'frontend_cart_show' => ['/cart/show', 'frontend_cart_show'],
            'frontend_cart_clear' => ['/cart/clear', 'frontend_cart_clear'],
        ];
    }
}
