<?php
namespace I18nUrl\View\Helper;

use Cake\View\Helper;
use I18nUrl\I18n\LocaleMiddleware;

class I18nRouteHelper extends Helper
{
    public function byName($name)
    {
        return ['_name' => $name . '.' . LocaleMiddleware::getLocale(true)];
    }
}
