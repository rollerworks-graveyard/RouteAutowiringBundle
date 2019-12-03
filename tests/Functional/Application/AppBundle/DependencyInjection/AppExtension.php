<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests\Functional\Application\AppBundle\DependencyInjection;

use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AppExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container): void
    {
        $routeImporter = new RouteImporter($container);
        $routeImporter->addObjectResource($this);

        $routeImporter->import('@AppBundle/Resources/config/routing/frontend.yml', 'frontend');

        if ($container->hasParameter('enable_backend') && $container->getParameter('enable_backend')) {
            $routeImporter->import('@AppBundle/Resources/config/routing/backend.yml', 'backend');
        }
    }
}
