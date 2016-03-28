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

use Rollerworks\Bundle\RouteAutowiringBundle\RouteResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * RouteAutowiringPass registers the all tagged route-resource definitions
 * as resolved RouteCollections for the RouteSlotLoader service.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
final class RouteAutowiringPass implements CompilerPassInterface
{
    const TAG_NAME = 'rollerworks_route_autowiring.route_resource';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('rollerworks_route_autowiring.route_loader') || !$container->has('routing.loader')) {
            return;
        }

        $serviceIds = $container->findTaggedServiceIds(self::TAG_NAME);

        /** @var ParameterBag $parameterBag */
        $parameterBag = $container->getParameterBag();
        /** @var Definition[] $slotServices */
        $slotServices = [];

        foreach ($serviceIds as $id => list($tag)) {
            $slot = $parameterBag->resolveString($tag['slot']);

            if (!isset($slotServices[$slot])) {
                $slotServices[$slot] = $this->createCollectionBuilder();
            }

            /** @var RouteResource $resource */
            $resource = $container->get($id);
            $slotServices[$slot]->addMethodCall('import', [$resource->getResource(), '/', $resource->getType()]);
        }

        /** @var Definition[] $loaders */
        $loaders = [];

        // The routing_slot services will be in-lined, but register them still so it's easier to write tests.
        foreach ($slotServices as $slot => $definition) {
            $container->setDefinition($id = 'rollerworks_route_autowiring.routing_slot.'.$slot, $definition);
            $loaders[$slot] = (new Definition(RouteCollection::class, []))->setFactory([new Reference($id), 'build']);
        }

        $container->getDefinition('rollerworks_route_autowiring.route_loader')->replaceArgument(0, $loaders);
    }

    private function createCollectionBuilder()
    {
        return (new Definition(RouteCollectionBuilder::class))
            ->setArguments([new Reference('routing.loader')])
            ->setPublic(false)
        ;
    }
}
