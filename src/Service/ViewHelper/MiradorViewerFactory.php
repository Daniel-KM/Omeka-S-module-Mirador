<?php

namespace Mirador\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Mirador\View\Helper\Mirador;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Service factory for the Mirador view helper.
 */
class MiradorFactory implements FactoryInterface
{
    /**
     * Create and return the Mirador view helper
     *
     * @return Mirador
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $currentTheme = $serviceLocator->get('Omeka\Site\ThemeManager')
            ->getCurrentTheme();
        return new Mirador($currentTheme);
    }
}
