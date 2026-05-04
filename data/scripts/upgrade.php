<?php declare(strict_types=1);

namespace Mirador;

use Common\Stdlib\PsrMessage;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$settings = $services->get('Omeka\Settings');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.85')) {
    $message = new \Omeka\Stdlib\Message(
        'The module %1$s should be upgraded to version %2$s or later.', // @translate
        'Common', '3.4.85'
    );
    $messenger->addError($message);
    throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $translator('Missing requirement. Unable to upgrade.')); // @translate
}

if (version_compare($oldVersion, '3.1.0', '<')) {
    $sql = <<<'SQL'
        DELETE FROM site_setting
        WHERE id IN ('mirador_class', 'mirador_style', 'mirador_locale');
        SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.1.3', '<')) {
    $sql = <<<'SQL'
        DELETE FROM site_setting
        WHERE id IN ("mirador_append_item_set_show", "mirador_append_item_show", "mirador_append_item_set_browse", "mirador_append_item_browse");
        SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.1.7', '<')) {
    $siteSettings = $services->get('Omeka\Settings\Site');
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $siteSettings->set('mirador_version', '2');
    }
}

if (version_compare($oldVersion, '3.3.7.3', '<')) {
    $settings->delete('mirador_manifest_property');
}

if (version_compare($oldVersion, '3.3.7.9', '<')) {
    $settings->set('mirador_plugins_2', $settings->get('mirador_plugins', []));
    $settings->set('mirador_plugins', []);
    $settings->set('mirador_config_item_2', $settings->get('mirador_config_item', null));
    $settings->set('mirador_config_item', null);
    $settings->set('mirador_config_collection_2', $settings->get('mirador_config_collection', null));
    $settings->set('mirador_config_collection', null);

    $siteSettings = $services->get('Omeka\Settings\Site');
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $siteSettings->set('mirador_plugins_2', $siteSettings->get('mirador_plugins', []));
        $siteSettings->set('mirador_plugins', []);
        $siteSettings->set('mirador_config_item_2', $siteSettings->get('mirador_config_item', null));
        $siteSettings->set('mirador_config_item', null);
        $siteSettings->set('mirador_config_collection_2', $siteSettings->get('mirador_config_collection', null));
        $siteSettings->set('mirador_config_collection', null);
    }
}

if (version_compare($oldVersion, '3.3.7.13', '<')) {
    $module = $services->get('Omeka\ModuleManager')->getModule('IiifServer');
    if ($module && version_compare($module->getIni('version') ?? '', '3.6.5.3', '<')) {
        $translator = $services->get('MvcTranslator');
        $message = new PsrMessage(
            'This module requires the module {module}, version {version} or above.', // @translate
            ['module' => 'IiifServer', 'version' => '3.6.5.3']
        );
        $messenger->addError($message);
        throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $translator('Missing requirement. Unable to upgrade.')); // @translate
    }

    $message = new PsrMessage(
        'The module supports audio and video for Mirador v3.' // @translate
    );
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.4.11', '<')) {
    $settings->set('mirador_plugins_3', $settings->get('mirador_plugins', []));
    $settings->set('mirador_plugins', []);
    $settings->set('mirador_config_item_3', $settings->get('mirador_config_item', null));
    $settings->set('mirador_config_item', null);
    $settings->set('mirador_config_collection_3', $settings->get('mirador_config_collection', null));
    $settings->set('mirador_config_collection', null);

    $siteSettings = $services->get('Omeka\Settings\Site');
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $siteSettings->set('mirador_plugins_3', $siteSettings->get('mirador_plugins', []));
        $siteSettings->set('mirador_plugins', []);
        $siteSettings->set('mirador_config_item_3', $siteSettings->get('mirador_config_item', null));
        $siteSettings->set('mirador_config_item', null);
        $siteSettings->set('mirador_config_collection_3', $siteSettings->get('mirador_config_collection', null));
        $siteSettings->set('mirador_config_collection', null);
    }

    $message = new PsrMessage(
        'The module supports Mirador v4.' // @translate
    );
    $messenger->addSuccess($message);

    $message = new PsrMessage(
        'Warning: if you customized theme, note that settings were renamed.' // @translate
    );
    $messenger->addWarning($message);
}

if (version_compare($oldVersion, '3.4.12', '<')) {
    $message = new PsrMessage(
        'Mirador v4 now uses ecmascript modules with import maps instead of pre-compiled bundles. Plugins are now loaded individually and locally, in a GDPR-compliand way.' // @translate
    );
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.4.14', '<')) {
    $siteSettings = $services->get('Omeka\Settings\Site');
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        // By default, keep item show only (previous implicit behavior).
        $siteSettings->set('mirador_placement', ['after/items']);
    }

    $message = new PsrMessage(
        'A new option allows to set the placement of the viewer on item show and browse pages.' // @translate
    );
    $messenger->addSuccess($message);

    // Force version to 4 when no custom plugins or config for v2/v3.
    $hasCustom = function ($settingsService) {
        $version = $settingsService->get('mirador_version', '4');
        if ($version === '4') {
            return false;
        }
        if (!empty($settingsService->get('mirador_plugins_2', []))) {
            return true;
        }
        if (!empty($settingsService->get('mirador_plugins_3', []))) {
            return true;
        }
        if ($settingsService->get('mirador_config_item_2') !== null) {
            return true;
        }
        if ($settingsService->get('mirador_config_collection_2') !== null) {
            return true;
        }
        $config3Item = $settingsService->get('mirador_config_item_3');
        if ($config3Item !== null && trim($config3Item) !== '' && trim($config3Item) !== '{}') {
            return true;
        }
        $config3Collection = $settingsService->get('mirador_config_collection_3');
        if ($config3Collection !== null && trim($config3Collection) !== '' && trim($config3Collection) !== '{}') {
            return true;
        }
        return false;
    };

    $skipped = [];

    if (!$hasCustom($settings)) {
        $settings->set('mirador_version', '4');
    } else {
        $skipped[] = 'settings';
    }

    $siteSettings = $services->get('Omeka\Settings\Site');
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        if (!$hasCustom($siteSettings)) {
            $siteSettings->set('mirador_version', '4');
        } else {
            $skipped[] = $site->slug();
        }
    }

    if ($skipped) {
        $message = new PsrMessage(
            'Mirador version was not updated to v4 for {list} because custom v2/v3 plugins or config were found. Please check and update manually.', // @translate
            ['list' => implode(', ', $skipped)]
        );
        $messenger->addWarning($message);
    } else {
        $message = new PsrMessage(
            'Mirador version has been set to v4 for all settings and sites.' // @translate
        );
        $messenger->addSuccess($message);
    }
}

if (version_compare($oldVersion, '3.4.17', '<')) {
    // Force migration v3 -> v4 for remaining users. Plugins are no longer a
    // reason to skip: the v3 plugin selection is copied to mirador_plugins
    // (v4) when the key exists in the v4 plugin list. Only a custom v3 JSON
    // config (mirador_config_item_3 / mirador_config_collection_3, non-empty
    // and not "{}") still skips the version update so the admin can review.
    $v4Plugins = array_keys(require dirname(__DIR__) . '/plugins/plugins.php');

    $migrate = function ($settingsService) use ($v4Plugins) {
        if ($settingsService->get('mirador_version', '4') !== '3') {
            return null;
        }
        $oldPlugins = $settingsService->get('mirador_plugins_3', []) ?: [];
        $newPlugins = array_values(array_intersect($oldPlugins, $v4Plugins));
        if ($newPlugins) {
            $settingsService->set('mirador_plugins', $newPlugins);
        }
        $config3Item = $settingsService->get('mirador_config_item_3');
        $config3Collection = $settingsService->get('mirador_config_collection_3');
        $hasCustomConfig = ($config3Item !== null && trim($config3Item) !== '' && trim($config3Item) !== '{}')
            || ($config3Collection !== null && trim($config3Collection) !== '' && trim($config3Collection) !== '{}');
        if ($hasCustomConfig) {
            return false;
        }
        $settingsService->set('mirador_version', '4');
        return true;
    };

    $skipped = [];
    $migrated = [];

    $result = $migrate($settings);
    if ($result === true) {
        $migrated[] = 'settings';
    } elseif ($result === false) {
        $skipped[] = 'settings';
    }

    $siteSettings = $services->get('Omeka\Settings\Site');
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $result = $migrate($siteSettings);
        if ($result === true) {
            $migrated[] = $site->slug();
        } elseif ($result === false) {
            $skipped[] = $site->slug();
        }
    }

    if ($skipped) {
        $message = new PsrMessage(
            'Mirador v3 kept for {list} due to a custom v3 JSON config. Review and migrate manually.', // @translate
            ['list' => implode(', ', $skipped)]
        );
        $messenger->addWarning($message);
    }

    if ($migrated) {
        $message = new PsrMessage(
            'Mirador forced to v4 for {list}. v3 plugins copied to v4 plugins. You may review the migration.', // @translate
            ['list' => implode(', ', $migrated)]
        );
        $messenger->addNotice($message);
    }

    $message = new PsrMessage(
        'Mirador v4 now includes all plugins from Mirador v3 and some other ones, in particular image cropper, ocr helper and physical ruler.' // @translate
    );
    $messenger->addSuccess($message);
}
