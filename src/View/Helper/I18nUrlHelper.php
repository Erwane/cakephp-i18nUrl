<?php
namespace I18nUrl\View\Helper;

use Cake\View\Helper;
use I18nUrl\Middleware\LocaleMiddleware;
use I18nUrl\Routing\Router;

class I18nUrlHelper extends Helper
{
    public $helpers = ['Html', 'Url'];

    public function getLocale($clean = false)
    {
        return LocaleMiddleware::getLocale($clean);
    }

    public function link($title, $url = null, array $options = [])
    {
        return $this->Html->link($title, $this->build($url), $options);
    }

    public function build($url = null, $options = false)
    {
        return $this->Url->build(Router::url($url, $options));
    }
}
