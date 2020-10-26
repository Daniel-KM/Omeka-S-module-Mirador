<?php
namespace Mirador\Service\Form;

use Interop\Container\ContainerInterface;
use Mirador\Form\SiteSettingsFieldset;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SiteSettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $siteSettings = $services->get('Omeka\Settings\Site');
        $miradorVersion = $siteSettings->get('mirador_version', '3');

        $plugins = require_once dirname(dirname(dirname(__DIR__)))
            . ($miradorVersion === '2' ? '/data/plugins/plugins-mirador-2.php' : '/data/plugins/plugins.php');

        $form = new SiteSettingsFieldset(null, $options);
        return $form
            ->setPlugins($plugins);
    }
}
