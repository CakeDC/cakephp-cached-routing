<?php
declare(strict_types=1);

/**
 * Copyright 2013 - 2022, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2013 - 2022, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\CachedRouting\Test\TestCase\Routing\Middleware;

use Cake\Cache\Cache;
use Cake\Http\ServerRequestFactory;
use Cake\Routing\Exception\FailedRouteCacheException;
use Cake\Routing\RouteBuilder;
use Cake\Routing\RouteCollection;
use Cake\TestSuite\TestCase;
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
}
