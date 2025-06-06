<?php declare(strict_types=1);

namespace Mirador;

if (!class_exists('Common\TraitModule', false)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

use Common\Stdlib\PsrMessage;
use Common\TraitModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;
use Omeka\Module\Exception\ModuleCannotInstallException;

/**
 * Mirador
 *
 * @copyright Daniel Berthereau, 2019-2025
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
        $translate = $plugins->get('translate');
        $translator = $services->get('MvcTranslator');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.67')) {
            $message = new \Omeka\Stdlib\Message(
                $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.67'
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
        }

        $js = __DIR__ . '/asset/vendor/mirador/mirador.min.js';
        if (!file_exists($js)) {
            throw new ModuleCannotInstallException((string) (new PsrMessage(
                'The library "{library]" should be installed. See module’s installation documentation.', // @translate
                ['library' => 'Mirador']
            ))->setTranslator($translator));
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
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

    public function handleViewBrowseAfterItem(Event $event): void
    {
        $view = $event->getTarget();
        $services = $this->getServiceLocator();
        // Note: there is no item-set show, but a special case for items browse.
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
        if (!$this->isModuleActive('IiifServer')) {
            return;
        }

        $view = $event->getTarget();
        echo $view->mirador($view->itemSets);
    }

    public function handleViewShowAfterItem(Event $event): void
    {
        // In Omeka S v4, if the player is set in the view, don't add it.
        $view = $event->getTarget();
        $services = $this->getServiceLocator();
        $currentTheme = $services->get('Omeka\Site\ThemeManager')->getCurrentTheme();
        $blockLayoutManager = $services->get('Omeka\ResourcePageBlockLayoutManager');
        $resourcePageBlocks = $blockLayoutManager->getResourcePageBlocks($currentTheme);
        foreach ($resourcePageBlocks['items'] ?? [] as $blocks) {
            if (in_array('mirador', $blocks)) {
                return;
            }
        }

        echo $view->mirador($view->item);
    }
}
