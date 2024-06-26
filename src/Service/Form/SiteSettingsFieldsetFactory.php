<?php declare(strict_types=1);

namespace Mirador\Service\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mirador\Form\SiteSettingsFieldset;

class SiteSettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $plugins = require dirname(__DIR__, 3) . '/data/plugins/plugins.php';
        $plugins2 = require dirname(__DIR__, 3) . '/data/plugins/plugins-mirador-2.php';

        $form = new SiteSettingsFieldset(null, $options ?? []);
        return $form
            ->setPlugins($plugins)
            ->setPlugins2($plugins2);
    }
}
