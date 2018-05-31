<?php
namespace I18nUrl\Routing;

use Cake\Routing\RouteBuilder as CakeRouteBuilder;

class RouteBuilder extends CakeRouteBuilder
{

    /**
     * connect a new route like Cake RouterBuilder but create
     * a named route foreach locales and return a RouteManipulator object
     * who can handle all route manipulations methods
     * @param string $route A string describing the template of the route
     * @param array|string $defaults An array describing the default route parameters. These parameters will be used by default
     *   and can supply routing parameters that are not dynamic. See above.
     * @param array $options An array matching the named elements in the route to regular expressions which that
     *   element should match. Also contains additional parameters such as which routed parameters should be
     *   shifted into the passed arguments, supplying patterns for routing parameters and supplying the name of a
     *   custom routing class.
     * @return \Cake\Routing\Route\Route
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
   public function connect($routes, $defaults = [], array $options = [])
   {
        if (!is_array($routes)) {
            $routes = array_fill_keys(Router::$locales, $routes);
        }

        foreach (Router::$locales as $locale) {
            if (!isset($options['_name'])) {
                $options['_name'] = substr(sha1(json_encode($defaults)), 0, 12);
            }

            // set vars for route
            $currentDefaults = $defaults;
            $currentOptions = $options;

            $currentOptions['_name'] .= '.' . $locale;

            $route = parent::connect($routes[$locale], $currentDefaults, $currentOptions);
        }

        return new RouteManipulator($options['_name'], $this->_collection);
   }
}
