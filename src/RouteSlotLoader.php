<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * RouteSlotLoader loads from pre-registered routing slots.
 */
final class RouteSlotLoader extends Loader
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $slots;

    /**
     * @var ResourceInterface[]
     */
    private $resources;

    /**
     * @param ResourceInterface[] $resources
     */
    public function __construct(ContainerInterface $container, array $resources = [])
    {
        $this->container = $container;
        $this->resources = $resources;
    }

    /**
     * Loads a RouteCollection from a routing slot.
     *
     * @return RouteCollection returns an empty RouteCollection object when no routes
     *                         are registered for the slot
     */
    public function load($resource, string $type = null): RouteCollection
    {
        if (!$this->container->has($resource)) {
            $collection = new RouteCollection();
        } else {
            $collection = $this->container->get($resource)->build();
        }

        foreach ($this->resources as $trackedResource) {
            $collection->addResource($trackedResource);
        }

        return $collection;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'rollerworks_autowiring' === $type;
    }
}
