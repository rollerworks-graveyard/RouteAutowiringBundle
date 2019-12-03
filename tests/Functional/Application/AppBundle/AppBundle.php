<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests\Functional\Application\AppBundle;

use Rollerworks\Bundle\RouteAutowiringBundle\Tests\Functional\Application\AppBundle\DependencyInjection\AppExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AppBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new AppExtension();
    }
}
