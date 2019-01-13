<?php

/*
 * Copyright 2015-2017 Daniel Berthereau
 * Copyright 2016-2017 BibLibre
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software. You can use, modify and/or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software’s author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user’s attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software’s suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace MiradorViewer;

use Omeka\Module\AbstractModule;
use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Module\Manager as ModuleManager;
use MiradorViewer\Form\ConfigForm;
use MiradorViewer\Form\SiteSettingsFieldset;
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
        $acl->allow(null, 'MiradorViewer\Controller\Player');
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $js = __DIR__ . '/asset/mirador/mirador.min.js';
        if (!file_exists($js)) {
            $t = $serviceLocator->get('MvcTranslator');
            throw new ModuleCannotInstallException(
                $t->translate('The MiradorViewer library should be installed.') // @translate
                    . ' ' . $t->translate('See module’s installation documentation.') // @translate
            );
        }

        $this->manageSettings($serviceLocator->get('Omeka\Settings'), 'install');
        $this->manageSiteSettings($serviceLocator, 'install');
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $this->manageSettings($serviceLocator->get('Omeka\Settings'), 'uninstall');
        $this->manageSiteSettings($serviceLocator, 'uninstall');
    }

    protected function manageSettings($settings, $process, $key = 'config')
    {
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)][$key];
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

    protected function manageSiteSettings(ServiceLocatorInterface $serviceLocator, $process)
    {
        $siteSettings = $serviceLocator->get('Omeka\Settings\Site');
        $api = $serviceLocator->get('Omeka\ApiManager');
        $sites = $api->search('sites')->getContent();
        foreach ($sites as $site) {
            $siteSettings->setTargetId($site->id());
            $this->manageSettings($siteSettings, $process, 'site_settings');
        }
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
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');
        $form = $services->get('FormElementManager')->get(ConfigForm::class);

        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        $data = $this->prepareDataToPopulate($settings, $defaultSettings);

        $view = $renderer;
        $html = '<p>';
        $html .= $this->iiifServerIsActive()
            ? $view->translate('The IIIF Server is active, so when no url is set, the viewer will use the standard routes.') // @translate
            : ($view->translate('The IIIF Server is not active, so when no url is set, the viewer won’t be displayed.') // @translate
                . ' ' . $view->translate('Furthermore, the Mirador Viewer can’t display lists of items.')); // @translate
        $html .= '</p>';
        $html .= '<p>'
            . $view->translate('The viewer itself can be configured in settings of each site, via the file "config.json" and the helper.') // @translate
            . '</p>';

        $form->init();
        $form->setData($data);
        $html .= $renderer->formCollection($form);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');
        $form = $services->get('FormElementManager')->get(ConfigForm::class);

        $params = $controller->getRequest()->getPost();

        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $params = $form->getData();
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        $params = array_intersect_key($params, $defaultSettings);
        foreach ($params as $name => $value) {
            $settings->set($name, $value);
        }
    }

    public function handleSiteSettings(Event $event)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings\Site');
        $space = strtolower(__NAMESPACE__);
        $form = $event->getTarget();
        $fieldset = $services->get('FormElementManager')->get(SiteSettingsFieldset::class);

        // The module iiif server is required to display collections of items.

        $data = $this->prepareDataToPopulate($settings, $config[$space]['site_settings']);

        $fieldset->setName($space);
        $form->add($fieldset);
        $form->get($space)->populateValues($data);
    }

    public function handleSiteSettingsFilters(Event $event)
    {
        $inputFilter = $event->getParam('inputFilter');
        $inputFilter->get('miradorviewer')->add([
            'name' => 'miradorviewer_append_item_set_browse',
            'required' => false,
        ]);
        $inputFilter->get('miradorviewer')->add([
            'name' => 'miradorviewer_append_item_browse',
            'required' => false,
        ]);
    }

    /**
     * @todo Use form methods to populate.
     * @param \Omeka\Settings\SettingsInterface $settings
     * @param array$defaultSettings
     * @return array
     */
    protected function prepareDataToPopulate(\Omeka\Settings\SettingsInterface $settings, array $defaultSettings)
    {
        $data = [];
        foreach ($defaultSettings as $name => $value) {
            $val = $settings->get($name, $value);
            if (is_array($value)) {
                $val = is_array($val) ? implode(PHP_EOL, $val) : $val;
            }
            $data[$name] = $val;
        }
        return $data;
    }

    public function handleViewBrowseAfterItem(Event $event)
    {
        // Note: there is no item-set show, but a special case for items browse.
        $view = $event->getTarget();
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $siteSettings = $services->get('Omeka\Settings\Site');
        if ($siteSettings->get(
            'miradorviewer_append_item_set_show',
            $config['miradorviewer']['site_settings']['miradorviewer_append_item_set_show']
        )) {
            echo $view->miradorViewer($view->itemSet);
        } elseif ($this->iiifServerIsActive()
            && $siteSettings->get(
                'miradorviewer_append_item_browse',
                $config['miradorviewer']['site_settings']['miradorviewer_append_item_browse']
            )
        ) {
            echo $view->miradorViewer($view->items);
        }
    }

    public function handleViewBrowseAfterItemSet(Event $event)
    {
        if (!$this->iiifServerIsActive()) {
            return;
        }

        $view = $event->getTarget();
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $siteSettings = $services->get('Omeka\Settings\Site');
        if ($siteSettings->get(
            'miradorviewer_append_item_set_browse',
            $config['miradorviewer']['site_settings']['miradorviewer_append_item_set_browse']
        )) {
            echo $view->miradorViewer($view->itemSets);
        }
    }

    public function handleViewShowAfterItem(Event $event)
    {
        $view = $event->getTarget();
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $siteSettings = $services->get('Omeka\Settings\Site');
        if ($siteSettings->get(
            'miradorviewer_append_item_show',
            $config['miradorviewer']['site_settings']['miradorviewer_append_item_show']
        )) {
            echo $view->miradorViewer($view->item);
        }
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
