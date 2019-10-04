<?php
namespace I18nUrl\Routing;

use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute as CakeDashedRoute;
use Cake\Utility\Hash;

class I18nRoute extends CakeDashedRoute
{
    /**
     * Constructor for a Route.
     *
     * @param string $template Template string with parameter placeholders
     * @param array $defaults Array of defaults for the route.
     * @param array $options Array of parameters and additional options for the Route
     *
     * @return void
     */
    public function __construct($template, $defaults = [], array $options = [])
    {
        if (!isset($options['lang'])) {
            return parent::__construct($template, $defaults, $options);
        }

        $routeLang = $options['lang'];
        $isDefault = $routeLang === Configure::read('I18n.default');

        if (strpos($template, ':lang') === false && !$isDefault) {
            $template = '/:lang' . $template;
        }
        if ($template === '/:lang/') {
            $template = '/:lang';
        }

        $options['inflect'] = 'dasherize';

        if ($isDefault) {
            unset($options['lang']);
        }

        parent::__construct($template, $defaults, $options);
    }

    public function translate(array $langs = [])
    {
        $routeName = substr($this->options['_name'], 0, strrpos($this->options['_name'], ':'));

        foreach ($this->paths as $lang => $path) {
            $newRouteName = $routeName . ':' . $lang;
            if ($newRouteName === $this->options['_name']) {
                continue;
            }

            $route = $langs[$lang] ?? $this->originaleRoute;
            $route = str_replace('//', '/', $path . $route);

            $url = $this->defaults;

            $options = $this->options;
            $options['_name'] = $newRouteName;
            $options['lang'] = $lang;

            $new = new I18nRoute($route, $url, $options);

            Router::getRouteCollection()->add($new, $options);
        }
    }
}
