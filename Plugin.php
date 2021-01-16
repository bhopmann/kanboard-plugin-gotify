<?php

namespace Kanboard\Plugin\Gotify;

use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;

/*
 * Gotify Plugin
 *
 * @package  Kanboard\Plugin\Gotify
 * @author   Benedikt Hopmann
 */
class Plugin extends Base
{
    public function initialize()
    {
        $this->template->hook->attach('template:config:integrations', 'gotify:config/integration');
        $this->template->hook->attach('template:project:integrations', 'gotify:project/integration');
        $this->template->hook->attach('template:user:integrations', 'gotify:user/integration');

        $this->userNotificationTypeModel->setType('gotify', t('Gotify'), '\Kanboard\Plugin\Gotify\Notification\Gotify');
        $this->projectNotificationTypeModel->setType('gotify', t('Gotify'), '\Kanboard\Plugin\Gotify\Notification\Gotify');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginDescription()
    {
        return t('Receive notifications on Gotify');
    }

    public function getPluginAuthor()
    {
        return 'Benedikt Hopmann';
    }

    public function getPluginVersion()
    {
        return '1.1.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/bhopmann/kanboard-plugin-gotify';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }
}
