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

        $slotsServiceIds = $container->findTaggedServiceIds(self::TAG_NAME);
        $slotsToServiceIds = [];

        /** @var ParameterBag $parameterBag */
        $parameterBag = $container->getParameterBag();
        /** @var Definition[] $slotServices */
        $slotServices = [];

        foreach ($slotsServiceIds as $id => list($tag)) {
            $slot = $parameterBag->resolveString($tag['slot']);

            if (!isset($slotServices[$slot])) {
                $slotServiceId = 'rollerworks_route_autowiring.routing_slot.'.$slot;
                $slotServices[$slot] = $container->setDefinition($slotServiceId, $this->createCollectionBuilder());
                $slotsToServiceIds[$slot] = $slotServiceId;
            }

            /** @var RouteResource $resource */
            $resource = $container->get($id);
            $slotServices[$slot]->addMethodCall('import', [$resource->getResource(), '/', $resource->getType()]);
            $container->removeDefinition($id);
        }

        $container->getDefinition('rollerworks_route_autowiring.route_loader')->replaceArgument(1, $slotsToServiceIds);
    }

    private function createCollectionBuilder()
    {
        return (new Definition(RouteCollectionBuilder::class))
            ->setArguments([new Reference('rollerworks_route_autowiring.resource_loader')])
            // Route collections are loaded lazily to circumvent the container
            // circular dependency of on this collection.
            ->setPublic(true)
        ;
    }
}
