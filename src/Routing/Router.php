<?php
declare(strict_types=1);

namespace I18nUrl\Routing;

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Router as CakeRouter;
use Cake\Routing\RouteBuilder as CakeRouteBuilder;
use I18nUrl\Middleware\LocaleMiddleware;
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
     * {@inheritDoc}
     */
    public static function createRouteBuilder(string $path, array $options = []): CakeRouteBuilder
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

    public static function url($url = null, bool $full = false): string
    {
        if (is_array($url)) {
            if (isset($url['_loop'])) {
                unset($url['_loop']);

                return parent::url($url, $full);
            } elseif (isset($url['_name'])) {
                // check if route exist without lang
                $namedRoutes = array_keys(Router::getRouteCollection()->named());
                if (!in_array($url['_name'], $namedRoutes)) {
                    // else, add lang name
                    $url['_name'] = $url['_name'] . ':' . LocaleMiddleware::getLocale(true);
                }
            }
        }

        return parent::url($url, $full);
    }

    public static function routeExists($url = null, bool $full = false): bool
    {
        // antiloop system
        if (is_array($url)) {
            $url['_loop'] = true;
        }

        return parent::routeExists($url);
    }
}
