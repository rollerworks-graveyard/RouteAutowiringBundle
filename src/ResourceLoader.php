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
use Symfony\Component\Config\Loader\LoaderResolverInterface;

/**
 * The ResourceLoader provides a service-level access to the
 * Routing loader resolver.
 *
 * @internal
 */
final class ResourceLoader extends Loader
{
    public function __construct(LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function load($resource, string $type = null)
    {
        return $this->import($resource, $type);
    }

    /**
     * Noop implementation, always returns false.
     */
    public function supports($resource, string $type = null): bool
    {
        return false;
    }
}
