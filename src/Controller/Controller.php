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

        return $response->withLocation(Router::url($event->getData(0)));
    }
}
