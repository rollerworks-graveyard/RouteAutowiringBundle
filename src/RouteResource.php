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

/**
 * RouteResource holds a route resource for the process.
 *
 * This class is only used for internally communication
 * and should not be used outside this package.
 *
 * @internal
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
final class RouteResource
{
    /**
     * @var string|array
     */
    private $resource;

    /**
     * @var string|null
     */
    private $type;

    public function __construct($resource, $type = null)
    {
        $this->resource = $resource;
        $this->type = $type;
    }

    /**
     * @return array|string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }
}
