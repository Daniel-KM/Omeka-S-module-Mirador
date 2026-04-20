<?php declare(strict_types=1);

namespace Mirador;

if (!class_exists('Common\TraitModule', false)) {
    require_once file_exists(dirname(__DIR__) . '/Common/src/TraitModule.php')
        ? dirname(__DIR__) . '/Common/src/TraitModule.php'
        : dirname(__DIR__) . '/Common/TraitModule.php';
}

use Common\Stdlib\PsrMessage;
use Common\TraitModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;
use Omeka\Module\Exception\ModuleCannotInstallException;

/**
 * Mirador.
 *
 * @copyright Daniel Berthereau, 2019-2026
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    use TraitModule;

    public function onBootstrap(MvcEvent $event): void
    {
        parent::onBootstrap($event);
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, ['Mirador\Controller\Player']);
    }

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $plugins = $services->get('ControllerPluginManager');
        $translator = $services->get('MvcTranslator');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.84')) {
            $message = new \Omeka\Stdlib\Message(
                $translator->translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.84'
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
        }

        $errors = [];

        $js = __DIR__ . '/asset/vendor/mirador/mirador.min.js';
        $jsEsm = __DIR__ . '/asset/vendor/mirador-esm/mirador.js';
        if (!file_exists($js) && !file_exists($jsEsm)) {
            $message = new PsrMessage(
                'The library "{library}" should be installed. See module’s installation documentation.', // @translate
                ['library' => 'Mirador']
            );
            $errors[] = (string) $message->setTranslator($translator);
        }

        if ($errors) {
            throw new ModuleCannotInstallException(implode("\n", $errors));
        }
    }

    protected function postInstall(): void
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        if ($settings->get('iiifserver_manifest_external_property') === null) {
            $settings->set('iiifserver_manifest_external_property', 'dcterms:hasFormat');
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        // Display viewer in resource pages for old themes (before v4.1
        // resource page blocks). The site is not set yet, so checks are
        // done in method.
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.after',
            [$this, 'handleViewShowAfterItem']
        );
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
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
    }

    public function handleViewShowAfterItem(Event $event): void
    {
        $services = $this->getServiceLocator();
        $currentTheme = $services->get('Omeka\Site\ThemeManager')->getCurrentTheme();
        if (method_exists($currentTheme, 'isConfigurableResourcePageBlocks') && $currentTheme->isConfigurableResourcePageBlocks()) {
            return;
        }

        $siteSettings = $services->get('Omeka\Settings\Site');
        $placements = $siteSettings->get('mirador_placement', ['after/items']);
        if (!in_array('after/items', $placements)) {
            return;
        }

        $view = $event->getTarget();
        echo $view->mirador($view->item);
    }

    public function handleViewBrowseAfterItem(Event $event): void
    {
        $services = $this->getServiceLocator();
        $currentTheme = $services->get('Omeka\Site\ThemeManager')->getCurrentTheme();
        if (method_exists($currentTheme, 'isConfigurableResourcePageBlocks') && $currentTheme->isConfigurableResourcePageBlocks()) {
            return;
        }

        $siteSettings = $services->get('Omeka\Settings\Site');
        $placements = $siteSettings->get('mirador_placement', ['after/items']);
        if (!in_array('browse/items', $placements)) {
            return;
        }

        $view = $event->getTarget();

        // Note: there is no item-set show, but a special case for items
        // browse inside an item set.
        $isItemSetShow = (bool) $services->get('Application')
            ->getMvcEvent()->getRouteMatch()->getParam('item-set-id');
        if ($isItemSetShow) {
            echo $view->mirador($view->itemSet);
        } elseif ($this->isModuleActive('IiifServer')) {
            echo $view->mirador($view->items);
        }
    }

    public function handleViewBrowseAfterItemSet(Event $event): void
    {
        $services = $this->getServiceLocator();
        $currentTheme = $services->get('Omeka\Site\ThemeManager')->getCurrentTheme();
        if (method_exists($currentTheme, 'isConfigurableResourcePageBlocks') && $currentTheme->isConfigurableResourcePageBlocks()) {
            return;
        }

        $siteSettings = $services->get('Omeka\Settings\Site');
        $placements = $siteSettings->get('mirador_placement', ['after/items']);
        if (!in_array('browse/item_sets', $placements)) {
            return;
        }

        if (!$this->isModuleActive('IiifServer')) {
            return;
        }

        $view = $event->getTarget();
        echo $view->mirador($view->itemSets);
    }
}
