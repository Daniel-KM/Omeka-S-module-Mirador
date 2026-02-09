<?php declare(strict_types=1);

namespace MiradorTest\Form;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\Form\SettingsFieldset;
use MiradorTest\MiradorTestTrait;

/**
 * Tests for the Mirador settings fieldset.
 */
class SettingsFieldsetTest extends AbstractHttpControllerTestCase
{
    use MiradorTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function testFieldsetIsInstantiable(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);
        $this->assertInstanceOf(SettingsFieldset::class, $fieldset);
    }

    public function testFieldsetLabel(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);
        $this->assertEquals('Mirador IIIF Viewer', $fieldset->getLabel());
    }

    public function testFieldsetId(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);
        $this->assertEquals('mirador', $fieldset->getAttribute('id'));
    }

    public function testFieldsetHasVersionElement(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);
        $this->assertTrue($fieldset->has('mirador_version'));
    }

    public function testVersionElementHasExpectedValueOptions(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $element = $fieldset->get('mirador_version');
        $valueOptions = $element->getValueOptions();
        $this->assertArrayHasKey('2', $valueOptions);
        $this->assertArrayHasKey('3', $valueOptions);
        $this->assertArrayHasKey('4', $valueOptions);
    }

    public function testFieldsetHasPluginsElements(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $this->assertTrue($fieldset->has('mirador_plugins'), 'Missing mirador_plugins (v4).');
        $this->assertTrue($fieldset->has('mirador_plugins_3'), 'Missing mirador_plugins_3.');
        $this->assertTrue($fieldset->has('mirador_plugins_2'), 'Missing mirador_plugins_2.');
    }

    public function testFieldsetHasConfigElements(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        // V4.
        $this->assertTrue($fieldset->has('mirador_config_item'));
        $this->assertTrue($fieldset->has('mirador_config_collection'));
        // V3.
        $this->assertTrue($fieldset->has('mirador_config_item_3'));
        $this->assertTrue($fieldset->has('mirador_config_collection_3'));
        // V2.
        $this->assertTrue($fieldset->has('mirador_config_item_2'));
        $this->assertTrue($fieldset->has('mirador_config_collection_2'));
    }

    public function testFieldsetHasAnnotationEndpointElement(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);
        $this->assertTrue($fieldset->has('mirador_annotation_endpoint'));
    }

    public function testFieldsetHasPreselectedItemsElement(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $this->assertTrue($fieldset->has('mirador_preselected_items'));
        $element = $fieldset->get('mirador_preselected_items');
        $this->assertInstanceOf(\Laminas\Form\Element\Number::class, $element);
        $this->assertEquals(0, $element->getAttribute('min'));
        $this->assertEquals(999, $element->getAttribute('max'));
    }

    public function testPlugins2AreLoadedByFactory(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $plugins2 = $fieldset->getPlugins2();
        $this->assertIsArray($plugins2);
        $this->assertNotEmpty($plugins2);
        $this->assertArrayHasKey('canvas-link', $plugins2);
    }

    public function testPlugins3AreLoadedByFactory(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $plugins3 = $fieldset->getPlugins3();
        $this->assertIsArray($plugins3);
        $this->assertNotEmpty($plugins3);
        $this->assertArrayHasKey('annotations', $plugins3);
        $this->assertArrayHasKey('dl', $plugins3);
    }

    public function testPlugins4AreLoadedByFactory(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $plugins = $fieldset->getPlugins();
        $this->assertIsArray($plugins);
        $this->assertNotEmpty($plugins, 'V4 plugins should not be empty.');
        $this->assertArrayHasKey('annotations', $plugins);
    }

    /**
     * Test that v3 plugins select uses the correct value options (not v4).
     */
    public function testPlugins3ElementUsesCorrectValueOptions(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $element = $fieldset->get('mirador_plugins_3');
        $valueOptions = $element->getOption('value_options');
        $this->assertNotEmpty($valueOptions, 'V3 plugins select should have value options.');
        $this->assertArrayHasKey('annotations', $valueOptions);
    }

    /**
     * Test that v4 plugins select uses the correct value options.
     */
    public function testPlugins4ElementUsesCorrectValueOptions(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SettingsFieldset::class);

        $element = $fieldset->get('mirador_plugins');
        $valueOptions = $element->getOption('value_options');
        $this->assertNotEmpty($valueOptions, 'V4 plugins select should have value options.');
        $this->assertArrayHasKey('annotations', $valueOptions);
    }
}
