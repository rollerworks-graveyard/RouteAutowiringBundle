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

use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/** @internal */
final class RouteCollectionBuilder
{
    /** @var LoaderInterface */
    private $loader;

    /**
     * @var Route[]|RouteCollectionBuilder[]
     */
    private $routes = [];

    /** @var string|null */
    private $prefix;

    /** @var ResourceInterface[] */
    private $resources = [];

    public function __construct(LoaderInterface $loader = null)
    {
        $this->loader = $loader;
    }

    public function import($resource, string $prefix = '/', string $type = null)
    {
        /** @var RouteCollection[] $collections */
        $collections = $this->load($resource, $type);

        $builder = new self($this->loader);

        foreach ($collections as $collection) {
            foreach ($collection->all() as $name => $route) {
                $builder->routes[$name] = $route;
            }

            foreach ($collection->getResources() as $resource) {
                $builder->resources[] = $resource;
            }
        }

        // mount into this builder
        $builder->prefix = trim(trim($prefix), '/');
        $this->routes[] = $builder;

        return $builder;
    }

    public function build(): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($this->routes as $name => $route) {
            if ($route instanceof Route) {
                if (null !== $this->prefix) {
                    $route->setPath('/'.$this->prefix.$route->getPath());
                }

                $routeCollection->add($name, $route);
            } else {
                /* @var self $route */
                $subCollection = $route->build();
                if (null !== $this->prefix) {
                    $subCollection->addPrefix($this->prefix);
                }

                $routeCollection->addCollection($subCollection);
            }
        }

        foreach ($this->resources as $resource) {
            $routeCollection->addResource($resource);
        }

        return $routeCollection;
    }

    /**
     * Finds a loader able to load an imported resource and loads it.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return RouteCollection[]
     *
     * @throws LoaderLoadException If no loader is found
     */
    private function load($resource, string $type = null): array
    {
        if (null === $this->loader) {
            throw new \BadMethodCallException('Cannot import other routing resources: you must pass a LoaderInterface when constructing RouteCollectionBuilder.');
        }

        if ($this->loader->supports($resource, $type)) {
            $collections = $this->loader->load($resource, $type);

            return \is_array($collections) ? $collections : [$collections];
        }

        if (null === $resolver = $this->loader->getResolver()) {
            throw new LoaderLoadException($resource, null, null, null, $type);
        }

        if (false === $loader = $resolver->resolve($resource, $type)) {
            throw new LoaderLoadException($resource, null, null, null, $type);
        }

        $collections = $loader->load($resource, $type);

        return \is_array($collections) ? $collections : [$collections];
    }
}
