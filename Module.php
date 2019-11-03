<?php

namespace Mirador;

use Mirador\Form\ConfigForm;
use Mirador\Form\SiteSettingsFieldset;
use Omeka\Module\AbstractModule;
use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Module\Manager as ModuleManager;
use Omeka\Settings\SettingsInterface;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, ['Mirador\Controller\Player']);
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);

        $js = __DIR__ . '/asset/vendor/mirador/mirador.min.js';
        if (!file_exists($js)) {
            $t = $serviceLocator->get('MvcTranslator');
            throw new ModuleCannotInstallException(
                $t->translate('The Mirador library should be installed.') // @translate
                    . ' ' . $t->translate('See module’s installation documentation.') // @translate
            );
        }

        $this->manageAnySettings($serviceLocator->get('Omeka\Settings'), 'config', 'install');
        $this->manageSiteSettings('install');
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->manageAnySettings($serviceLocator->get('Omeka\Settings'), 'config', 'uninstall');
        $this->manageSiteSettings('uninstall');
    }

    protected function manageSiteSettings($process)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings\Site');
        $api = $services->get('Omeka\ApiManager');
        $sites = $api->search('sites')->getContent();
        foreach ($sites as $site) {
            $settings->setTargetId($site->id());
            $this->manageAnySettings($settings, 'site_settings', $process);
        }
    }

    /**
     * Set or delete all settings of a specific type.
     *
     * @param SettingsInterface $settings
     * @param string $settingsType
     * @param string $process
     */
    protected function manageAnySettings(SettingsInterface $settings, $settingsType, $process)
    {
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)][$settingsType];
        foreach ($defaultSettings as $name => $value) {
            switch ($process) {
                case 'install':
                    $settings->set($name, $value);
                    break;
                case 'uninstall':
                    $settings->delete($name);
                    break;
            }
        }
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        require_once 'data/scripts/upgrade.php';
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.browse.after',
            [$this, 'handleViewBrowseAfterItem']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Site\ItemSet',
            'view.browse.after',
            [$this, 'handleViewBrowseAfterItemSet']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.after',
            [$this, 'handleViewShowAfterItem']
        );

        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_input_filters',
            [$this, 'handleSiteSettingsFilters']
        );
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();

        $settings = $services->get('Omeka\Settings');
        $data = $this->prepareDataToPopulate($settings, 'config');

        $view = $renderer;
        $html = '<p>';
        $html .= $this->iiifServerIsActive()
            ? $view->translate('The IIIF Server is active, so when no url is set, the viewer will use the standard routes.') // @translate
            : ($view->translate('The IIIF Server is not active, so when no url is set, the viewer won’t be displayed.') // @translate
                . ' ' . $view->translate('Furthermore, the Mirador Viewer can’t display lists of items.')); // @translate
        $html .= '</p>';
        $html .= '<p>'
            . $view->translate('The viewer itself can be basically configured in settings of each site, or in the theme.') // @translate
            . '</p>';

        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($data);
        $html .= $renderer->formCollection($form);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $config = include __DIR__ . '/config/module.config.php';
        $space = strtolower(__NAMESPACE__);

        $services = $this->getServiceLocator();

        $params = $controller->getRequest()->getPost();

        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $params = $form->getData();

        $settings = $services->get('Omeka\Settings');
        $defaultSettings = $config[$space]['config'];
        $params = array_intersect_key($params, $defaultSettings);
        foreach ($params as $name => $value) {
            $settings->set($name, $value);
        }
        return true;
    }

    public function handleSiteSettings(Event $event)
    {
        $services = $this->getServiceLocator();

        // The module iiif server is required to display collections of items.

        $settingType = 'site_settings';
        $settings = $services->get('Omeka\Settings\Site');
        $data = $this->prepareDataToPopulate($settings, $settingType);

        $space = strtolower(__NAMESPACE__);

        $fieldset = $services->get('FormElementManager')->get(SiteSettingsFieldset::class);
        $fieldset->setName($space);
        $form = $event->getTarget();
        $form->add($fieldset);
        $form->get($space)->populateValues($data);
    }

    public function handleSiteSettingsFilters(Event $event)
    {
        $event->getParam('inputFilter')
            ->get('mirador')
            ->add([
                'name' => 'mirador_plugins',
                'required' => false,
            ])
        ;
    }

    /**
     * Prepare data for a form or a fieldset.
     *
     * To be overridden by module for specific keys.
     *
     * @todo Use form methods to populate.
     * @param SettingsInterface $settings
     * @param string $settingsType
     * @return array
     */
    protected function prepareDataToPopulate(SettingsInterface $settings, $settingsType)
    {
        $config = include __DIR__ . '/config/module.config.php';
        $space = strtolower(__NAMESPACE__);
        if (empty($config[$space][$settingsType])) {
            return;
        }

        $defaultSettings = $config[$space][$settingsType];

        $data = [];
        foreach ($defaultSettings as $name => $value) {
            $val = $settings->get($name, $value);
            $data[$name] = $val;
        }

        return $data;
    }

    public function handleViewBrowseAfterItem(Event $event)
    {
        $view = $event->getTarget();
        $services = $this->getServiceLocator();
        // Note: there is no item-set show, but a special case for items browse.
        $isItemSetShow = (bool) $services->get('Application')
            ->getMvcEvent()->getRouteMatch()->getParam('item-set-id');
        if ($isItemSetShow) {
            echo $view->mirador($view->itemSet);
        } elseif ($this->iiifServerIsActive()) {
            echo $view->mirador($view->items);
        }
    }

    public function handleViewBrowseAfterItemSet(Event $event)
    {
        if (!$this->iiifServerIsActive()) {
            return;
        }

        $view = $event->getTarget();
        echo $view->mirador($view->itemSets);
    }

    public function handleViewShowAfterItem(Event $event)
    {
        $view = $event->getTarget();
        echo $view->mirador($view->item);
    }

    protected function iiifServerIsActive()
    {
        static $iiifServerIsActive;

        if (is_null($iiifServerIsActive)) {
            $module = $this->getServiceLocator()
                ->get('Omeka\ModuleManager')
                ->getModule('IiifServer');
            $iiifServerIsActive = $module && $module->getState() === ModuleManager::STATE_ACTIVE;
        }
        return $iiifServerIsActive;
    }
}
