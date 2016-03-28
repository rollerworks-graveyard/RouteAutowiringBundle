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

use Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\Compiler\RouteAutowiringPass;
use Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\RouteAutowiringExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The RouteImporter registers route-imports in the service-container
 * for later processing the RouteAutowiringPass.
 *
 * Usage of this class is very straightforward.
 * Add the following in your bundle extension class:
 *
 * $routeImporter = new RouteImporter($containerBuilder);
 * $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/frontend.yml', 'frontend');
 * $routeImporter->import('@AcmeSomethingBundle/Resources/config/routing/backend.yml', 'backend', 'yaml');
 *
 * The `[...]/frontend.yml` resource is registered in the frontend routing-slot.
 * The `[...]/backend.yml` resource is registered in the backend routing-slot (with the type set explicitly).
 *
 * Then your routing file you load the `frontend` resource with type `rollerworks_autowiring`.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
final class RouteImporter
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var string|null
     */
    private $defaultSlot;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container   The ContainerBuilder instance
     *                                      for registering the service definitions on.
     * @param string|null      $defaultSlot Default slot for resources
     *                                      (can be overwritten per resource).
     */
    public function __construct(ContainerBuilder $container, $defaultSlot = null)
    {
        $this->container = $container;
        $this->defaultSlot = $defaultSlot;
    }

    /**
     * Import a routing file into to the routing-slot.
     *
     * The route resource is registered as private service.
     *
     * @param string|array $resource Resource to import (depends on the actual type).
     * @param string|null  $slot     The routing-slot to import to.
     *                               Uses the default when none is provided.
     * @param string|null  $type     The type of the resource (optional),
     *                               required if the type is not auto guessable.
     *
     * @throws \InvalidArgumentException When no (default) slot is provided.
     *
     * @return self
     */
    public function import($resource, $slot = null, $type = null)
    {
        if (null === $slot) {
            $slot = $this->defaultSlot;
        }

        if (null === $slot) {
            throw new \InvalidArgumentException(sprintf('No slot provided for resource "%s".', $resource));
        }

        $hash = sha1($slot.'~'.$resource.($type ? '~'.$type : ''));

        $this->container->register(RouteAutowiringExtension::EXTENSION_ALIAS.'.'.$hash, RouteResource::class)
            ->setPublic(false)
            ->setArguments([$resource, $type])
            ->addTag(RouteAutowiringPass::TAG_NAME, ['slot' => $slot]);

        return $this;
    }
}
