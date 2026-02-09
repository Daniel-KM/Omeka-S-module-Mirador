<?php declare(strict_types=1);

namespace MiradorTest\View\Helper;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\View\Helper\IiifManifestExternal;
use MiradorTest\MiradorTestTrait;

/**
 * Tests for the IiifManifestExternal view helper.
 */
class IiifManifestExternalTest extends AbstractHttpControllerTestCase
{
    use MiradorTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function testViewHelperIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $viewHelperManager = $services->get('ViewHelperManager');
        $this->assertTrue($viewHelperManager->has('iiifManifestExternal'));
    }

    public function testViewHelperIsInstantiable(): void
    {
        $services = $this->getServiceLocator();
        $viewHelperManager = $services->get('ViewHelperManager');
        $helper = $viewHelperManager->get('iiifManifestExternal');
        $this->assertInstanceOf(IiifManifestExternal::class, $helper);
    }

    public function testHelperExtendsAbstractHelper(): void
    {
        $helper = new IiifManifestExternal();
        $this->assertInstanceOf(\Laminas\View\Helper\AbstractHelper::class, $helper);
    }
}
