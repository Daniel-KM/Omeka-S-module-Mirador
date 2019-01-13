<?php

namespace MiradorViewer\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use MiradorViewer\View\Helper\MiradorViewer;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Service factory for the MiradorViewer view helper.
 */
class MiradorViewerFactory implements FactoryInterface
{
    /**
     * Create and return the MiradorViewer view helper
     *
     * @return MiradorViewer
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $currentTheme = $serviceLocator->get('Omeka\Site\ThemeManager')
            ->getCurrentTheme();
        return new MiradorViewer($currentTheme);
    }
}
