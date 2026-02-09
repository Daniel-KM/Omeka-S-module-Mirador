<?php declare(strict_types=1);

namespace MiradorTest;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\Site\BlockLayout\Mirador as MiradorBlockLayout;
use Mirador\Site\ResourcePageBlockLayout\Mirador as MiradorResourcePageBlockLayout;
use Mirador\View\Helper\IiifManifestExternal;

/**
 * Tests for Mirador module configuration and service registration.
 */
class ConfigTest extends AbstractHttpControllerTestCase
{
    use MiradorTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function testModuleIsActive(): void
    {
        $services = $this->getServiceLocator();
        $moduleManager = $services->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule('Mirador');
        $this->assertNotNull($module, 'Module Mirador should be registered.');
        $this->assertEquals('active', $module->getState(), 'Module Mirador should be active.');
    }

    public function testConfigHasSettings(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $this->assertArrayHasKey('mirador', $config);
        $this->assertArrayHasKey('config', $config['mirador']);
        $this->assertArrayHasKey('settings', $config['mirador']);
        $this->assertArrayHasKey('site_settings', $config['mirador']);
    }

    public function testDefaultSettingsValues(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $osdDefault = <<<'JS'
            {
                "osdConfig": {
                    "maxZoomPixelRatio": 10
                }
            }
            JS;

        $settings = $config['mirador']['settings'];
        $this->assertEquals('4', $settings['mirador_version']);
        $this->assertEquals([], $settings['mirador_plugins']);
        $this->assertEquals($osdDefault, $settings['mirador_config_item']);
        $this->assertEquals($osdDefault, $settings['mirador_config_collection']);
        $this->assertEquals([], $settings['mirador_plugins_3']);
        $this->assertEquals($osdDefault, $settings['mirador_config_item_3']);
        $this->assertEquals($osdDefault, $settings['mirador_config_collection_3']);
        $this->assertEquals([], $settings['mirador_plugins_2']);
        $this->assertNull($settings['mirador_config_item_2']);
        $this->assertNull($settings['mirador_config_collection_2']);
        $this->assertEquals('', $settings['mirador_annotation_endpoint']);
        $this->assertEquals(0, $settings['mirador_preselected_items']);
    }

    public function testDefaultSiteSettingsValues(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $siteSettings = $config['mirador']['site_settings'];
        $this->assertEquals('4', $siteSettings['mirador_version']);
        $this->assertFalse($siteSettings['mirador_skip_default_css']);
        $this->assertEquals(0, $siteSettings['mirador_preselected_items']);
    }

    public function testConfigDefaultExternalManifestProperty(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $this->assertEquals(
            'dcterms:hasFormat',
            $config['mirador']['config']['iiifserver_manifest_external_property']
        );
    }

    public function testViewHelpersAreRegistered(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $invokables = $config['view_helpers']['invokables'] ?? [];
        $this->assertArrayHasKey('iiifManifestExternal', $invokables);
        $this->assertEquals(IiifManifestExternal::class, $invokables['iiifManifestExternal']);

        $factories = $config['view_helpers']['factories'] ?? [];
        $this->assertArrayHasKey('mirador', $factories);
    }

    public function testBlockLayoutIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $layouts = $config['block_layouts']['invokables'];
        $this->assertArrayHasKey('mirador', $layouts);
        $this->assertEquals(MiradorBlockLayout::class, $layouts['mirador']);
    }

    public function testResourcePageBlockLayoutIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $layouts = $config['resource_page_block_layouts']['invokables'];
        $this->assertArrayHasKey('mirador', $layouts);
        $this->assertEquals(MiradorResourcePageBlockLayout::class, $layouts['mirador']);
    }

    public function testFormElementsAreRegistered(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $invokables = $config['form_elements']['invokables'] ?? [];
        $this->assertArrayHasKey(\Mirador\Form\ConfigForm::class, $invokables);

        $factories = $config['form_elements']['factories'] ?? [];
        $this->assertArrayHasKey(\Mirador\Form\SettingsFieldset::class, $factories);
        $this->assertArrayHasKey(\Mirador\Form\SiteSettingsFieldset::class, $factories);
    }

    public function testControllerIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $invokables = $config['controllers']['invokables'];
        $this->assertArrayHasKey('Mirador\Controller\Player', $invokables);

        $aliases = $config['controllers']['aliases'];
        $this->assertArrayHasKey('Mirador\Controller\Item', $aliases);
        $this->assertArrayHasKey('Mirador\Controller\ItemSet', $aliases);
        $this->assertArrayHasKey('Mirador\Controller\CleanUrlController', $aliases);
    }

    public function testRoutesAreConfigured(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $routes = $config['router']['routes'];

        // Legacy top-level route.
        $this->assertArrayHasKey('mirador_player', $routes);
        $this->assertStringContainsString(
            'mirador',
            $routes['mirador_player']['options']['route']
        );

        // Site child routes.
        $siteChildRoutes = $routes['site']['child_routes'];
        $this->assertArrayHasKey('resource-id-mirador', $siteChildRoutes);
        $this->assertStringContainsString(
            'mirador',
            $siteChildRoutes['resource-id-mirador']['options']['route']
        );
    }

    public function testViewTemplatePathIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $templatePaths = $config['view_manager']['template_path_stack'];
        $moduleViewPath = realpath(dirname(__DIR__, 2) . '/view');
        $found = false;
        foreach ($templatePaths as $path) {
            if (realpath($path) === $moduleViewPath) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Module view path should be in template_path_stack.');
    }

    public function testTranslatorIsConfigured(): void
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');

        $this->assertArrayHasKey('translator', $config);
        $patterns = $config['translator']['translation_file_patterns'];
        $moduleLanguagePath = realpath(dirname(__DIR__, 2) . '/language');
        $found = false;
        foreach ($patterns as $pattern) {
            if (isset($pattern['base_dir']) && realpath($pattern['base_dir']) === $moduleLanguagePath) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Module language path should be in translation_file_patterns.');
    }

    public function testMiradorJsAssetExists(): void
    {
        $modulePath = dirname(__DIR__, 2);
        $this->assertFileExists($modulePath . '/asset/vendor/mirador/mirador.min.js');
    }

    public function testPluginsDataFilesExist(): void
    {
        $modulePath = dirname(__DIR__, 2);
        $this->assertFileExists($modulePath . '/data/plugins/plugins.php');
        $this->assertFileExists($modulePath . '/data/plugins/plugins-mirador-2.php');
        $this->assertFileExists($modulePath . '/data/plugins/plugins-mirador-3.php');
    }

    public function testPlugins2DataFileReturnsArray(): void
    {
        $plugins = require dirname(__DIR__, 2) . '/data/plugins/plugins-mirador-2.php';
        $this->assertIsArray($plugins);
        $this->assertNotEmpty($plugins);
        $this->assertArrayHasKey('canvas-link', $plugins);
        $this->assertArrayHasKey('download-menu', $plugins);
        $this->assertArrayHasKey('keyboard-navigation', $plugins);
    }

    public function testPlugins3DataFileReturnsArray(): void
    {
        $plugins = require dirname(__DIR__, 2) . '/data/plugins/plugins-mirador-3.php';
        $this->assertIsArray($plugins);
        $this->assertArrayHasKey('annotations', $plugins);
        $this->assertArrayHasKey('dl', $plugins);
        $this->assertArrayHasKey('image-tools', $plugins);
    }

    public function testViewTemplatesExist(): void
    {
        $modulePath = dirname(__DIR__, 2);
        $this->assertFileExists($modulePath . '/view/common/mirador.phtml');
        $this->assertFileExists($modulePath . '/view/common/block-layout/mirador.phtml');
        $this->assertFileExists($modulePath . '/view/common/resource-page-block-layout/mirador.phtml');
        $this->assertFileExists($modulePath . '/view/mirador/player/play.phtml');
    }
}
