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

use Rollerworks\Bundle\RouteAutowiringBundle\Tests\Functional\Application\AppKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    protected static function createKernel(array $options = [])
    {
        return new Application\AppKernel(
            isset($options['config']) ? $options['config'] : 'default.yml'
        );
    }

    protected static function getKernelClass()
    {
        return AppKernel::class;
    }

    protected static function newClient(array $options = [], array $server = []): KernelBrowser
    {
        $client = static::createClient(array_merge(['config' => 'default.yml'], $options), $server);

        $warmer = $client->getContainer()->get('cache_warmer');
        $warmer->warmUp($client->getContainer()->getParameter('kernel.cache_dir'));
        $warmer->enableOptionalWarmers();

        return $client;
    }
}
