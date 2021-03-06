<?php
use Cake\Routing\Router;

Router::addUrlFilter(function ($params, $request) {
    if (!isset($params['lang']) && !is_null($request) && $request->getParam('lang') !== false) {
        $params['lang'] = $request->getParam('lang');
    }

    return $params;
});
