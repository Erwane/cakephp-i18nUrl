<?php
namespace I18nUrl\View\Helper;

use Cake\View\Helper;
use I18nUrl\I18n\LocaleMiddleware;

class I18nUrlHelper extends Helper
{
    public function byName($name)
    {
        return ['_name' => $name . '.' . $this->getLocale(true)];
    }

    public function getLocale($clean = false)
    {
        return LocaleMiddleware::getLocale($clean);
    }
}
