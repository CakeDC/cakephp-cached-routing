<?php
declare(strict_types=1);

/**
 * Copyright 2013 - 2023, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2013 - 2023, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\CachedRouting\Routing\Middleware;

use Cake\Cache\Cache;
use Cake\Core\PluginApplicationInterface;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteCollection;
use Cake\Routing\Router;
use Cake\Routing\RoutingApplicationInterface;
use CakeDC\CachedRouting\Routing\Exception\FailedRouteCacheException;
use Exception;
use InvalidArgumentException;

/**
 * Cache the route collection contents to speed up route loading.
 * IMPORTANT: It will only work correctly for serializable route collections
 */
class CachedRoutingMiddleware extends RoutingMiddleware
{
    /**
     * Key used to store the route collection in the cache engine
     *
     * @var string
     */
    public const ROUTE_COLLECTION_CACHE_KEY = 'routeCollection';

    /**
     * The cache configuration name to use for route collection caching,
     * null to disable caching
     *
     * @var string|null
     */
    protected ?string $cacheConfig;

    /**
     * Constructor
     *
     * @param \Cake\Routing\RoutingApplicationInterface $app The application instance that routes are defined on.
     * @param string|null $cacheConfig The cache config name to use or null to disable routes cache
     */
    public function __construct(RoutingApplicationInterface $app, ?string $cacheConfig = null)
    {
        parent::__construct($app);
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * Trigger the application's and plugin's routes() hook.
     *
     * @return void
     */
    protected function loadRoutes(): void
    {
        $routeCollection = $this->buildRouteCollection();
        Router::setRouteCollection($routeCollection);
    }

    /**
     * Check if route cache is enabled and use the configured Cache to 'remember' the route collection
     *
     * @return \Cake\Routing\RouteCollection
     */
    protected function buildRouteCollection(): RouteCollection
    {
        if (Cache::enabled() && $this->cacheConfig !== null) {
            try {
                return Cache::remember(static::ROUTE_COLLECTION_CACHE_KEY, function () {
                    return $this->prepareRouteCollection();
                }, $this->cacheConfig);
            } catch (InvalidArgumentException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new FailedRouteCacheException(
                    'Unable to cache route collection. Cached routes must be serializable. Check for route-specific
                    middleware or other unserializable settings in your routes. The original exception message can
                    show what type of object failed to serialize.',
                    null,
                    $e
                );
            }
        }

        return $this->prepareRouteCollection();
    }

    /**
     * Generate the route collection using the builder
     *
     * @return \Cake\Routing\RouteCollection
     */
    protected function prepareRouteCollection(): RouteCollection
    {
        $builder = Router::createRouteBuilder('/');
        $this->app->routes($builder);
        if ($this->app instanceof PluginApplicationInterface) {
            $this->app->pluginRoutes($builder);
        }

        return Router::getRouteCollection();
    }
}
