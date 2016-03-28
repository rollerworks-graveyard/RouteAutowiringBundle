<?php

/*
 * This file is part of the Rollerworks RouteAutowiringBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutowiringBundle\Tests;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\Compiler\RouteAutowiringPass;
use Rollerworks\Bundle\RouteAutowiringBundle\DependencyInjection\RouteAutowiringExtension;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteResource;

final class RouteImporterTest extends AbstractContainerBuilderTestCase
{
    /**
     * @test
     */
    public function it_imports_a_resource_for_a_slot()
    {
        $importer = new RouteImporter($this->container);
        $importer->import('@AcmeSomething/Resources/config/routing.yml', 'main');

        $serviceId = RouteAutowiringExtension::EXTENSION_ALIAS.'.'.sha1('main~@AcmeSomething/Resources/config/routing.yml');

        $this->assertContainerBuilderHasService($serviceId, RouteResource::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, 0, '@AcmeSomething/Resources/config/routing.yml');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, 1, null);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            RouteAutowiringPass::TAG_NAME,
            ['slot' => 'main']
        );

        $this->assertFalse(
            $this->container->getDefinition($serviceId)->isPublic(),
            'Routing collections need to be private'
        );
    }

    /**
     * @test
     */
    public function it_imports_a_resource_for_a_slot_with_resource_type()
    {
        $importer = new RouteImporter($this->container);
        $importer->import('@AcmeSomething/Resources/config/routing.yml', 'main', 'yaml');

        $serviceId = RouteAutowiringExtension::EXTENSION_ALIAS.'.'.sha1('main~@AcmeSomething/Resources/config/routing.yml~yaml');

        $this->assertContainerBuilderHasService($serviceId, RouteResource::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, 0, '@AcmeSomething/Resources/config/routing.yml');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, 1, 'yaml');
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            RouteAutowiringPass::TAG_NAME,
            ['slot' => 'main']
        );

        $this->assertFalse(
            $this->container->getDefinition($serviceId)->isPublic(),
            'Routing collections need to be private'
        );
    }

    /**
     * @test
     */
    public function it_imports_a_resource_for_a_default_slot()
    {
        $importer = new RouteImporter($this->container, 'main');
        $importer->import('@AcmeSomething/Resources/config/routing.yml');

        $serviceId = RouteAutowiringExtension::EXTENSION_ALIAS.'.'.sha1('main~@AcmeSomething/Resources/config/routing.yml');
        $this->assertContainerBuilderHasService($serviceId, RouteResource::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            RouteAutowiringPass::TAG_NAME,
            ['slot' => 'main']
        );
    }

    /**
     * @test
     */
    public function it_imports_multiple_resources_for_slot()
    {
        $importer = new RouteImporter($this->container);
        $importer->import('@AcmeSomething/Resources/config/frontend.yml', 'frontend');
        $importer->import('@AcmeFoo/Resources/config/frontend.yml', 'frontend');
        $importer->import('@AcmeSomething/Resources/config/backend.yml', 'backend');

        $serviceId = RouteAutowiringExtension::EXTENSION_ALIAS.'.'.sha1('frontend~@AcmeSomething/Resources/config/frontend.yml');
        $this->assertContainerBuilderHasService($serviceId, RouteResource::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            RouteAutowiringPass::TAG_NAME,
            ['slot' => 'frontend']
        );

        $serviceId = RouteAutowiringExtension::EXTENSION_ALIAS.'.'.sha1('frontend~@AcmeFoo/Resources/config/frontend.yml');
        $this->assertContainerBuilderHasService($serviceId, RouteResource::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            RouteAutowiringPass::TAG_NAME,
            ['slot' => 'frontend']
        );

        $serviceId = RouteAutowiringExtension::EXTENSION_ALIAS.'.'.sha1('backend~@AcmeSomething/Resources/config/backend.yml');
        $this->assertContainerBuilderHasService($serviceId, RouteResource::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            $serviceId,
            RouteAutowiringPass::TAG_NAME,
            ['slot' => 'backend']
        );
    }
}
