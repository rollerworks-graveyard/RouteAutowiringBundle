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

use Rollerworks\Bundle\RouteAutowiringBundle\RouteCollectionBuilder;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RouteAutowiringPass registers the all tagged route-resource definitions
 * as resolved RouteCollections for the RouteSlotLoader service.
 */
final class RouteAutowiringPass implements CompilerPassInterface
{
    public const TAG_NAME = 'rollerworks_route_autowiring.route_resource';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('rollerworks_route_autowiring.route_loader') || !$container->has('routing.loader')) {
            return;
        }

        /** @var Definition[] $slotServices */
        $slotServices = [];
        /** @var Reference[] $slotsToServiceRefs */
        $slotsToServiceRefs = [];

        $parameterBag = $container->getParameterBag();

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $id => [$tag]) {
            $slot = $parameterBag->resolveValue($tag['slot']);

            if (!isset($slotServices[$slot])) {
                $slotServiceId = 'rollerworks_route_autowiring.routing_slot.'.$slot;
                $slotServices[$slot] = $container->setDefinition($slotServiceId, $this->createCollectionBuilder());
                $slotsToServiceRefs[$slot] = new Reference($slotServiceId);
            }

            /** @var RouteResource $resource */
            $resource = $container->get($id);
            $slotServices[$slot]->addMethodCall('import', [$resource->getResource(), '/', $resource->getType()]);
            $container->removeDefinition($id);
        }

        $routeLoaderDef = $container->getDefinition('rollerworks_route_autowiring.route_loader');
        $routeLoaderDef->replaceArgument(0, ServiceLocatorTagPass::register($container, $slotsToServiceRefs));
    }

    private function createCollectionBuilder(): Definition
    {
        return (new Definition(RouteCollectionBuilder::class))
            ->setArguments([new Reference('rollerworks_route_autowiring.resource_loader')])
            ->setPublic(false)
        ;
    }
}
