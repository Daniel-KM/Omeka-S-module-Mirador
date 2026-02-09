<?php declare(strict_types=1);

namespace MiradorTest\Form;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\Form\SettingsFieldset;
use Mirador\Form\SiteSettingsFieldset;
use MiradorTest\MiradorTestTrait;

/**
 * Tests for the Mirador site settings fieldset.
 */
class SiteSettingsFieldsetTest extends AbstractHttpControllerTestCase
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
        $fieldset = $formElementManager->get(SiteSettingsFieldset::class);
        $this->assertInstanceOf(SiteSettingsFieldset::class, $fieldset);
    }

    public function testFieldsetExtendsSettingsFieldset(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SiteSettingsFieldset::class);
        $this->assertInstanceOf(SettingsFieldset::class, $fieldset);
    }

    public function testFieldsetHasSkipDefaultCssElement(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SiteSettingsFieldset::class);
        $this->assertTrue($fieldset->has('mirador_skip_default_css'));
    }

    public function testSkipDefaultCssIsCheckbox(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SiteSettingsFieldset::class);
        $element = $fieldset->get('mirador_skip_default_css');
        $this->assertInstanceOf(\Laminas\Form\Element\Checkbox::class, $element);
    }

    public function testFieldsetInheritsAllParentElements(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SiteSettingsFieldset::class);

        // All parent elements should be present.
        $this->assertTrue($fieldset->has('mirador_version'));
        $this->assertTrue($fieldset->has('mirador_plugins'));
        $this->assertTrue($fieldset->has('mirador_plugins_3'));
        $this->assertTrue($fieldset->has('mirador_plugins_2'));
        $this->assertTrue($fieldset->has('mirador_annotation_endpoint'));
        $this->assertTrue($fieldset->has('mirador_preselected_items'));
        // Plus the site-specific element.
        $this->assertTrue($fieldset->has('mirador_skip_default_css'));
    }

    public function testFieldsetLabel(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $fieldset = $formElementManager->get(SiteSettingsFieldset::class);
        $this->assertEquals('Mirador IIIF Viewer', $fieldset->getLabel());
    }
}
