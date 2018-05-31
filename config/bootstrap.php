<?php

use Cake\Event\EventManager;

EventManager::instance()->on('Controller.beforeRedirect',
    ['priority' => 999],
    ['I18nUrl\Controller\Controller', 'beforeRedirect']
);
