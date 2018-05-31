<?php
namespace I18nUrl\Routing;

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Router as CakeRouter;
use LogicException;

class Router extends CakeRouter
{
    public static $locales = ['en'];

    private static $_defaultLocale = 'en';

    private static $_routes = [];

    private static $_scopeSet = false;

    /**
     * set available locales
     * @param array $locales ex. ['fr', 'en', 'pt']
     */
    public static function setLocales(array $locales)
    {
        self::$locales = $locales;
    }

    /**
     * default locale
     * @param string $locale ex. 'fr' or 'en'
     * @throws LogicException if default locale don't exists in locales
     */
    public static function setDefaultLocale(string $locale)
    {
        if (array_search($locale, self::$locales) === false) {
            throw new LogicException("Locale $locale is not set", 1);
        }

        self::$_defaultLocale = $locale;
    }

    /**
     * Create a RouteBuilder for the provided path.
     *
     * @param string $path The path to set the builder to.
     * @param array $options The options for the builder
     * @return \Cake\Routing\RouteBuilder
     */
    public static function createRouteBuilder($path, array $options = [])
    {
        $defaults = [
            'routeClass' => static::defaultRouteClass(),
            'extensions' => static::$_defaultExtensions,
        ];
        $options += $defaults;

        return new RouteBuilder(static::$_collection, $path, [], [
            'routeClass' => $options['routeClass'],
            'extensions' => $options['extensions'],
        ]);
    }
}
