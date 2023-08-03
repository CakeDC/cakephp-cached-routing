CakeDC Cached Routing
=====================

After deprecation of the cache option in RoutingMiddleware in CakePHP 4.4+ and removal in CakePHP 5.0, the feature is
extracted here in case you still want to use it to speed up your routes loading when your routes do not include
non-serializable contents.

This plugin provides a replacement for the RoutingMiddleware to allow caching of the route collection.

Installation
------------

* `composer require cakedc/cakephp-cached-routing`
* Replace RoutingMiddleware reference in your `Application::middleware` function to

```php
    // ...
    ->add(new \CakeDC\CachedRouting\Routing\Middleware\CachedRoutingMiddleware($this, '_cake_routes_'))
    // ...
```
* Add the `_cake_routes_` cache settings in your `config/app_local.php`.

Requirements
------------

* CakePHP 5.0+
* PHP 8.1+

Support
-------

For bugs and feature requests, please use the [issues](https://github.com/CakeDC/cakephp-cached-routing/issues) section of this repository.

Commercial support is also available, [contact us](https://www.cakedc.com/contact) for more information.

Contributing
------------

This repository follows the [CakeDC Plugin Standard](https://www.cakedc.com/plugin-standard). If you'd like to contribute new features, enhancements or bug fixes to the plugin, please read our [Contribution Guidelines](https://www.cakedc.com/contribution-guidelines) for detailed instructions.

License
-------

Copyright 2023 Cake Development Corporation (CakeDC). All rights reserved.

Licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) License. Redistributions of the source code included in this repository must retain the copyright notice found in each file.
