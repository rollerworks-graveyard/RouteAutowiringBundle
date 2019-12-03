<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RouteResourcePass registers the route resources on RouteSlotLoader service.
 */
final class RouteResourcePass implements CompilerPassInterface
{
    const TAG_NAME = 'rollerworks_route_autowiring.tracked_resource';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('rollerworks_route_autowiring.route_loader') || !$container->getParameter('kernel.debug')) {
            return;
        }

        $trackedResources = array_map(
            function ($id) {
                return new Reference($id);
            },
            array_keys($container->findTaggedServiceIds(self::TAG_NAME))
        );

        $container->getDefinition('rollerworks_route_autowiring.route_loader')->replaceArgument(2, $trackedResources);
    }
}
