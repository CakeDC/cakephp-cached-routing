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
namespace CakeDC\CachedRouting\Test\App;

use Cake\Core\HttpApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Response;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application implements \Cake\Routing\RoutingApplicationInterface, HttpApplicationInterface
{
    public function __construct()
    {
        Router::reload();
    }

    /**
     * @inheritDoc
     */
    public function routes(RouteBuilder $routes): void
    {
        $routes->connect('/articles', ['controller' => 'Articles', 'action' => 'index']);
    }

    public function bootstrap(): void
    {
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}
