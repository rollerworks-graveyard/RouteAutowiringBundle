Rollerworks RouteAutowiringBundle
=================================

The RollerworksRouteAutoWiringBundle allows to import multiple route collections
using an autowiring system.

For example you have BundleA which defines some routes, to use them you
need to explicitly import them, but if you remove/disable the bundle the
application breaks because it can't find the resource files anymore.

When you automatically enable them you can't keep the prefix consistent.
And sometimes you rather don't want to enable all route collections.

In practice you define your routes as normal, but instead of importing them
from a file or service you load them using the autowiring system.

Requirements
------------

You need at least PHP 5.5 and the Symfony FrameworkBundle (3.0+).

Installation
------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ php composer.phar require rollerworks/route-autowiring-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Rollerworks\Bundle\RouteAutowiringBundle\RollerworksRouteAutowiringBundle(),
            // ...
        ];

        // ...
    }

    // ...
}
```

Basic usage
-----------

There are two parts to this bundle, registering and loading.

Routing schema's are kept per "routing-slot", which are automatically
registered whenever you import a route collection.

### Registering routes for loading

Say you have an `AcmeShopBundle` with the following route collections:

```yaml
# Resources/config/routing/frontend.yml

_products:
    resource: "routing/frontend/products.yml"
    prefix:   /products

_cart:
    resource: "routing/frontend/cart.yml"
    prefix:   /cart
```

```yaml
# Resources/config/routing/frontend.yml

_products:
    resource: "routing/backend/products.yml"
    prefix:   /products
```

You can import them using the following snippet:

```php
use Rollerworks\Bundle\RouteAutoWiringBundle\RouteImporter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AcmeShopExtension extends Extension
{
    // ...

    public function load(array $configs, ContainerBuilder $container);
    {
        // ...

        $routeImporter = new RouteImporter($container);
        $routeImporter->import('@AcmeShopBundle/Resources/config/routing/frontend.yml', 'frontend');
        $routeImporter->import('@AcmeShopBundle/Resources/config/routing/backend.yml', 'backend');
    }

    // ...
}
```

The `@AcmeShopBundle/Resources/config/routing/frontend.yml` resource will be imported (*or registered*)
in the 'frontend' routing-slot. And the `@AcmeShopBundle/Resources/config/routing/backend.yml`
resource will be imported in the 'backend' routing slot.

**Note:**

> Route resources follow the same logic as the Symfony routing system,
> `@AcmeShopBundle` resolves the to full path of the AcmeShopBundle.
>
> See also: [Including External Routing Resources](https://symfony.com/doc/current/book/routing.html#including-external-routing-resources)

**Tip:** You can import multiple routing resources per slot :+1:

```php
$routeImporter->import('@AcmeShopBundle/Resources/config/routing/frontend.yml', 'frontend');
$routeImporter->import('@AcmeShopBundle/Resources/config/routing/extra.yml', 'frontend');
```

The import will try to guess the correct resource-type, but for special ones (like a service)
you need to provide the type in the third parameter:

```php
$routeImporter->import('@AcmeShopBundle/Resources/config/routing/frontend.yml', 'frontend', 'yaml');
```

Import multiple resources to the same slot? Provide a default (for the current instance):

```php
$routeImporter = new RouteImporter($container, 'frontend');
$routeImporter->import('@AcmeShopBundle/Resources/config/routing/frontend.yml'); // is imported in the frontend slot
$routeImporter->import('@AcmeShopBundle/Resources/config/routing/backend.yml', 'backend'); // is imported in the backend slot
```

### Loading registered routes

Ones your routes are imported into there routing-slot's it's time
to load them into the application's routing schema.

Normally you would use something like this:

```yaml
# app/config/routing.yml

_frontend:
    resource: "frontend.yml"
    prefix:   /

_backend:
    resource: "frontend.yml"
    prefix:   /
```

```yaml
# frontend.yml

_AcmeShop:
    resource: "@AcmeShopBundle/Resources/config/routing/frontend.yml"
```

```yaml
# backend.yml

_AcmeShop:
    resource: "@AcmeShopBundle/Resources/config/routing/backend.yml"
```

But instead you load them using the autowiring loader:

```yaml
# app/config/routing.yml

_frontend:
    resource: "frontend"
    type: rollerworks_autowiring
    prefix:   /

_backend:
    resource: "backend"
    type: rollerworks_autowiring
    prefix: backend/
```

That's it! All the routes that were imported in the 'frontend' and 'backend' slots
are now loaded into the applications routing schema.

**But wait, what if there are no routes imported for the slot?**

Then nothing happens, this bundle is designed to make configuration easy.
So when there are routes imported for the slot it simple returns an empty Collection,
which in practice is never used.

### 3rd part import example

As the Symfony routing system allows to load any route resource from a routing file
you can actually load a routing-slot from within from another routing slot.

Say you want to allow others to "extend" your bundle's routing schema:

First import the main parts in the application's routing file:

```yaml
# app/config/routing.yml

_frontend:
    resource: "frontend"
    type: rollerworks_autowiring
    prefix:   /

_backend:
    resource: "frontend"
    type: rollerworks_autowiring
    prefix: backend/
```

In AcmeShopBundle define the following routes,
and import them (*extension snippet omitted*).

```yaml
# Resources/config/routing/frontend.yml

_products:
    resource: "routing/frontend/products.yml"
    prefix:   /products

_cart:
    resource: "routing/frontend/cart.yml"
    prefix:   /cart
```

```yaml
# Resources/config/routing/frontend.yml

_products:
    resource: "routing/backend/products.yml"
    prefix:   /products

# Load other routing schema's from the routing-slot
_imports:
    resource: "acme_shop.frontend"
    type: rollerworks_autowiring
```

Others can now easily import there routing schema(s) into the
`acme_shop.frontend` routing-slot.

Versioning
----------

For transparency and insight into the release cycle, and for striving
to maintain backward compatibility, this package is maintained under
the Semantic Versioning guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

For more information on SemVer, please visit <http://semver.org/>.

License
-------

The package is provided under the [MIT license](LICENSE).
