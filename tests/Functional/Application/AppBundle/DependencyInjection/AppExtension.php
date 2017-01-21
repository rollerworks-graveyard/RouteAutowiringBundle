<?php

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests\Functional\Application\AppBundle\DependencyInjection;

use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AppExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $routeImporter = new RouteImporter($container);
        $routeImporter->addObjectResource($this);

        $routeImporter->import('@AppBundle/Resources/config/routing/frontend.yml', 'frontend');

        if ($container->hasParameter('enable_backend') && $container->getParameter('enable_backend')) {
            $routeImporter->import('@AppBundle/Resources/config/routing/backend.yml', 'backend');
        }
    }
}
