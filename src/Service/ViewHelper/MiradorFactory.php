<?php declare(strict_types=1);

namespace Mirador\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mirador\View\Helper\Mirador;

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
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $currentTheme = $services->get('Omeka\Site\ThemeManager')
            ->getCurrentTheme();
        return new Mirador($currentTheme);
    }
}
