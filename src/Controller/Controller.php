<?php
namespace I18nUrl\Controller;

use Cake\Event\Event;
use Cake\Http\Response;
use I18nUrl\Routing\Router;

class Controller
{
    public function beforeRedirect(Event $event)
    {
        $response = $event->getData(1);

        $route = $event->getData(0);

        if (Router::routeExists($route)) {
            $route['_loop'] = true;
        }

        $url = Router::url($route);

        return $response->withLocation($url);
    }
}
