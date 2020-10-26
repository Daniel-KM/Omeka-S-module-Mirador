<?php
namespace Mirador;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $serviceLocator
 * @var string $oldVersion
 * @var string $newVersion
 */
$services = $serviceLocator;

/**
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var array $config
 * @var array $config
 * @var \Omeka\Mvc\Controller\Plugin\Api $api
 */
$settings = $services->get('Omeka\Settings');
$connection = $services->get('Omeka\Connection');
$config = require dirname(dirname(__DIR__)) . '/config/module.config.php';
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');

if (version_compare($oldVersion, '3.1.0', '<')) {
    $sql = <<<SQL
DELETE FROM site_setting
WHERE id IN ('mirador_class', 'mirador_style', 'mirador_locale');
SQL;
    $connection->exec($sql);
}

if (version_compare($oldVersion, '3.1.3', '<')) {
    $sql = <<<'SQL'
DELETE FROM site_setting
WHERE id IN ("mirador_append_item_set_show", "mirador_append_item_show", "mirador_append_item_set_browse", "mirador_append_item_browse");
SQL;
    $connection->exec($sql);
}

if (version_compare($oldVersion, '3.1.7', '<')) {
    $siteSettings = $serviceLocator->get('Omeka\Settings\Site');
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $siteSettings->set('mirador_version', '2');
    }
}
