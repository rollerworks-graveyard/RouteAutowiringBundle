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

use Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\RouteAutowiringExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RollerworksRouteAutowiringBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new RouteAutowiringExtension();
        }

        return $this->extension;
    }

    protected function getContainerExtensionClass()
    {
        return RouteAutowiringExtension::class;
    }
}
