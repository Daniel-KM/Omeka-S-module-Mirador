<?php declare(strict_types=1);

namespace MiradorTest\Form;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\Form\ConfigForm;
use MiradorTest\MiradorTestTrait;

/**
 * Tests for the Mirador config form.
 */
class ConfigFormTest extends AbstractHttpControllerTestCase
{
    use MiradorTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function testConfigFormIsInstantiable(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $form = $formElementManager->get(ConfigForm::class);
        $this->assertInstanceOf(ConfigForm::class, $form);
    }

    public function testConfigFormHasExternalPropertyElement(): void
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $form = $formElementManager->get(ConfigForm::class);
        $this->assertTrue($form->has('iiifserver_manifest_external_property'));
    }
}
