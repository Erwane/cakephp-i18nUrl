<?php
// debug(__FILE__);

use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use I18nUrl\Routing\Router as I18nRouter;

Router::addUrlFilter(function ($params, $request) {
    if (!isset($params['lang']) && !is_null($request) && $request->getParam('lang') !== false) {
        $params['lang'] = $request->getParam('lang');
    } elseif (!isset($params['lang'])) {
        $params['lang'] = 'fr'; // set your default language here
    }

    return $params;
});

