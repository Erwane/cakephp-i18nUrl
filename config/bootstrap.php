<?php

use Cake\Core\Configure;
use Cake\Event\EventManager;

EventManager::instance()->on('Controller.beforeRedirect',
    ['priority' => 999],
    ['I18nUrl\Controller\Controller', 'beforeRedirect']
);

if (!Configure::read('I18n.languages')) {
    Configure::write('I18n.languages', ['en']);
}
if (!Configure::read('I18n.default')) {
    Configure::write('I18n.default', 'en');
}
