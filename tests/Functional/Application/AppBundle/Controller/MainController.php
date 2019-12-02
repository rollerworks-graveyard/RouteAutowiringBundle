<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests\Functional\Application\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MainController
{
    public function fooAction(Request $request)
    {
        return new Response('Route: '.$request->attributes->get('_route'));
    }
}
