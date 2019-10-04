<?php
namespace I18nUrl\Routing;

use Cake\Core\Configure;
use Cake\Routing\RouteBuilder as CakeRouteBuilder;

class RouteBuilder extends CakeRouteBuilder
{
    /**
     * {@inheritDoc}
     */
    public function scope($path, $params, $callback = null)
    {
        $lang = \Locale::getPrimaryLanguage(Configure::read('App.defaultLocale'));
        $currentLangPath = [ $lang => $path ];

        $i18nPath = $this->_params['_i18nPath'] ?? [];

        if (is_array($params)) {
            if (isset($params['_i18n'])) {
                $currentLangPath = array_merge($currentLangPath, $params['_i18n']);
            }
        }

        $i18nPath[] = $currentLangPath;
        $oldParams = $this->_params;

        $this->_params['_i18nPath'] = $i18nPath;
        $scope = parent::scope($path, $params, $callback);

        $this->_params = $oldParams;

        return $scope;
    }

    /**
     * {@inheritDoc}
     */
    public function connect($route, $defaults = [], array $options = [])
   {
        if (empty($options['routeClass'])) {
            $options['routeClass'] = $this->_routeClass;
        }

        if ($options['routeClass'] !== I18nRoute::class) {
            return parent::connect($route, $defaults, $options);
        }

        $defaultLang = \Locale::getPrimaryLanguage(Configure::read('App.defaultLocale'));

        // set a name if don't exists
        if (!isset($options['_name'])) {
            $options['_name'] = substr(sha1(json_encode($defaults)), 0, 12);
        }

        // append default language to route name
        $options['_name'] .= ':' . $defaultLang;
        $options['lang'] = $defaultLang;

        $paths = [];
        foreach (Configure::read('I18n.languages') as $allowedLang) {
            $paths[$allowedLang] = '';
            foreach ($this->_params['_i18nPath'] as $langs) {
                if (isset($langs[$allowedLang])) {
                    $paths[$allowedLang] .= $langs[$allowedLang];
                } else {
                    $paths[$allowedLang] .= $langs[$defaultLang];
                }
            }

            $paths[$allowedLang] = str_replace('//', '/', $paths[$allowedLang]);
        }

        $newRoute = parent::connect($route, $defaults, $options);
        $newRoute->originaleRoute = $route;
        $newRoute->paths = $paths;

        return $newRoute;
    }

    /**
     * Unset unused params before make route.
     *
     * {@inheritDoc}
     */
    protected function _makeRoute($route, $defaults, $options)
    {
        $oldParams = $this->_params;
        unset($this->_params['_i18n'], $this->_params['_i18nPath']);

        $route = parent::_makeRoute($route, $defaults, $options);

        $this->_params = $oldParams;

        return $route;
    }
}
