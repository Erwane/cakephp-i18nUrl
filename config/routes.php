<?php
// debug(__FILE__);

use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use I18nUrl\Routing\Router as I18nRouter;

Router::addUrlFilter(function ($params, $request) {
    if(isset($request->params['lang']) && !isset($params['lang'])) {
        $params['lang'] = $request->params['lang'];
    } elseif (!isset($params['lang'])) {
        $params['lang'] = 'fr'; // set your default language here
    }

    return $params;
});

