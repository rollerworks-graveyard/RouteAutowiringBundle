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

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * RouteSlotLoader loads from pre-registered routing slots.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
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
     * Constructor.
     *
     * @param ContainerInterface  $container
     * @param string[]            $slots
     * @param ResourceInterface[] $resources
     */
    public function __construct(ContainerInterface $container, array $slots, array $resources = [])
    {
        $this->slots = $slots;
        $this->container = $container;
        $this->resources = $resources;
    }

    /**
     * Loads a RouteCollection from a routing slot.
     *
     * @param mixed       $resource Some value that will resolve to a callable
     * @param string|null $type     The resource type
     *
     * @return RouteCollection returns an empty RouteCollection object when noå routes
     *                         are registered for the slot
     */
    public function load($resource, $type = null)
    {
        if (!isset($this->slots[$resource])) {
            $collection = new RouteCollection();
        } else {
            $collection = $this->container->get($this->slots[$resource])->build();
        }

        foreach ($this->resources as $trackedResource) {
            $collection->addResource($trackedResource);
        }

        return $collection;
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return $type === 'rollerworks_autowiring';
    }
}
