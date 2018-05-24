<?php
namespace I18nUrl\Routing;

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Router as CakeRouter;
use Cake\Routing\RouteBuilder;
use LogicException;

class Router
{
    private static $_locales = ['en'];

    private static $_defaultLocale = 'en';

    private static $_routes = [];

    private static $_scopeSet = false;

    /**
     * set available locales
     * @param array $locales ex. ['fr', 'en', 'pt']
     */
    public static function setLocales(array $locales)
    {
        self::$_locales = $locales;
    }

    /**
     * default locale
     * @param string $locale ex. 'fr' or 'en'
     * @throws LogicException if default locale don't exists in locales
     */
    public static function setDefaultLocale(string $locale)
    {
        if (array_search($locale, self::$_locales) === false) {
            throw new LogicException("Locale $locale is not set", 1);
        }

        self::$_defaultLocale = $locale;
    }

    public static function connect($routes, $defaults = [], array $options = [])
    {
        if (!is_array($routes)) {
            $routes = array_fill_keys(self::$_locales, $routes);
        }

        foreach (self::$_locales as $locale) {
            // set vars for route
            $currentDefaults = $defaults;
            $currentOptions = $options;

            if (!isset($currentOptions['_name'])) {
                debug('TODO NO ROUTE NAME');
                exit;
            }

            $currentOptions['_name'] .= '.' . $locale;

            self::$_routes[] = [
                ':lang' => $locale,
                'route' => $routes[$locale],
                'defaults' => $currentDefaults,
                'options' => $currentOptions,
            ];
        }
    }

    public static function connectAll()
    {
        if (static::$_scopeSet) {
            return;
        }

        $routes = self::$_routes;

        CakeRouter::scope('/:lang', function (RouteBuilder $builder) use ($routes) {
            foreach ($routes as $route) {
                $builder
                    ->connect($route['route'], $route['defaults'], $route['options'])
                    ->setPatterns(['lang' => $route[':lang']]);
            }
            $builder->fallbacks(DashedRoute::class);
        });

        static::$_scopeSet = true;
    }
}
