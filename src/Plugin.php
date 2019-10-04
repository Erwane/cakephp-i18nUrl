<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         DebugKit 3.15.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace I18nUrl;

use Cake\Core\BasePlugin;
use Cake\Routing\Middleware\RoutingMiddleware;
use I18nUrl\Middleware\LocaleMiddleware;

/**
 * Plugin class for CakePHP 3.6.0 plugin collection.
 */
class Plugin extends BasePlugin
{
    /**
     * Add middleware
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The queue
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware($middlewareQueue)
    {
        $middleware = new LocaleMiddleware();
        $middlewareQueue->insertAfter(RoutingMiddleware::class, $middleware);

        return $middlewareQueue;
    }
}
