<?php
namespace I18nUrl\View\Helper;

use Cake\View\Helper;
use I18nUrl\I18n\LocaleMiddleware;

class I18nUrlHelper extends Helper
{
    public $helpers = ['Html', 'Url'];

    public function byName($name)
    {
        return ['_name' => $name . '.' . $this->getLocale(true)];
    }

    public function getLocale($clean = false)
    {
        return LocaleMiddleware::getLocale($clean);
    }

    public function link($title, $url = null, array $options = [])
    {
        if (is_array($url) && isset($url['_name'])) {
            $localized = $this->byName($url['_name']);
            $url['_name'] = $localized['_name'];
        }

        return $this->Html->link($title, $url, $options);
    }

    public function build($url = null, $options = false)
    {
        if (is_array($url) && isset($url['_name'])) {
            $localized = $this->byName($url['_name']);
            $url['_name'] = $localized['_name'];
        }

        return $this->Url->build($url, $options);
    }
}
