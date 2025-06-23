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

namespace CakeDC\CachedRouting\Test\TestCase\Routing\Middleware;

use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\Routing\RouteBuilder;
use Cake\Routing\RouteCollection;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakeDC\CachedRouting\Routing\Exception\FailedRouteCacheException;
use CakeDC\CachedRouting\Routing\Middleware\CachedRoutingMiddleware;
use CakeDC\CachedRouting\Test\App\Application;
use CakeDC\CachedRouting\Test\App\TestRequestHandler;
use CakeDC\CachedRouting\Test\App\UnserializableMiddleware;

class CachedRoutingMiddlewareTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        Cache::enable();
        if (in_array('_cake_router_', Cache::configured(), true)) {
            Cache::clear('_cake_router_');
        }
        Cache::drop('_cake_router_');
    }

    /**
     * Test we store route collection in cache.
     */
    public function testCacheRoutes(): void
    {
        $cacheConfigName = '_cake_router_';
        Cache::setConfig($cacheConfigName, [
            'engine' => 'File',
            'path' => CACHE,
        ]);
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/articles']);
        $middleware = new CachedRoutingMiddleware(new Application(), $cacheConfigName);
        $middleware->process($request, new TestRequestHandler());

        $routeCollection = Cache::read('routeCollection', $cacheConfigName);
        $this->assertInstanceOf(RouteCollection::class, $routeCollection);
    }

    public function testFailedRouteCache(): void
    {
        Cache::setConfig('_cake_router_', [
            'engine' => 'File',
            'path' => CACHE,
        ]);

        $app = $this->createMock(Application::class);
        $app
            ->method('routes')
            ->will($this->returnCallback(function (RouteBuilder $routes) use ($app) {
                return $routes->registerMiddleware('should fail', new UnserializableMiddleware($app));
            }));

        $middleware = new CachedRoutingMiddleware($app, '_cake_router_');
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/articles']);

        $this->expectException(FailedRouteCacheException::class);
        $this->expectExceptionMessage('Unable to cache route collection.');
        $middleware->process($request, new TestRequestHandler());
    }

    /**
     * Test that when cache returns a non-RouteCollection value, it gets deleted and a new RouteCollection is created.
     */
    public function testInvalidCachedValue(): void
    {
        $cacheConfigName = '_cake_router_';
        Cache::setConfig($cacheConfigName, [
            'engine' => 'File',
            'path' => CACHE,
        ]);

        Cache::write(CachedRoutingMiddleware::ROUTE_COLLECTION_CACHE_KEY, 'not a route collection', $cacheConfigName);
        $this->assertEquals('not a route collection', Cache::read(CachedRoutingMiddleware::ROUTE_COLLECTION_CACHE_KEY, $cacheConfigName));

        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/articles']);
        $middleware = new CachedRoutingMiddleware(new Application(), $cacheConfigName);
        $response = $middleware->process($request, new TestRequestHandler());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNull(Cache::read(CachedRoutingMiddleware::ROUTE_COLLECTION_CACHE_KEY, $cacheConfigName));
        $this->assertInstanceOf(RouteCollection::class, Router::getRouteCollection());
    }
}
