<?php
namespace MiradorViewer\Service\Form;

use Interop\Container\ContainerInterface;
use MiradorViewer\Form\SiteSettingsFieldset;
use Zend\ServiceManager\Factory\FactoryInterface;

class SiteSettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $moduleManager = $container->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule('IiifServer');
        $iiifServerIsActive = $module && $module->getState() == \Omeka\Module\Manager::STATE_ACTIVE;

        $form = new SiteSettingsFieldset(null, $options);
        $form->setIiifServerIsActive($iiifServerIsActive);
        return $form;
    }
}
