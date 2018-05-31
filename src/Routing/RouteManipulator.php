<?php
namespace I18nUrl\Routing;

use Cake\Routing\RouteCollection;

class RouteManipulator
{
    private $_name;

    private $_collection;

    public function __construct(string $name, RouteCollection $collection)
    {
        $this->_name = $name;
        $this->_collection = $collection;
    }

    public function __call($method, $args)
    {
        // Names of routes
        $names = [];
        foreach (Router::$locales as $locale) {
            $names[$this->_name . '.' . $locale] = true;
        }

        // named routes from collection
        $named = $this->_collection->named();

        // For intersection key (name), execute $method with args
        foreach (array_intersect_key($named, $names) as $name => $route) {
            call_user_func_array([$route, $method], $args);
        }

        return $this;
    }
}
