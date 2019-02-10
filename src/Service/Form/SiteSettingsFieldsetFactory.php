<?php
namespace Mirador\Service\Form;

use Interop\Container\ContainerInterface;
use Mirador\Form\SiteSettingsFieldset;
use Zend\ServiceManager\Factory\FactoryInterface;

class SiteSettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $moduleManager = $container->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule('IiifServer');
        $iiifServerIsActive = $module && $module->getState() == \Omeka\Module\Manager::STATE_ACTIVE;

        $plugins = require_once dirname(dirname(dirname(__DIR__))) . '/data/plugins/plugins.php';

        $form = new SiteSettingsFieldset(null, $options);
        return $form
            ->setIiifServerIsActive($iiifServerIsActive)
            ->setPlugins($plugins);
    }
}
