<?php declare(strict_types=1);

namespace MiradorTest\View\Helper;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\View\Helper\Mirador;
use MiradorTest\MiradorTestTrait;

/**
 * Tests for the Mirador view helper.
 */
class MiradorTest extends AbstractHttpControllerTestCase
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
        $this->assertTrue($viewHelperManager->has('mirador'));
    }

    public function testViewHelperIsInstantiable(): void
    {
        $services = $this->getServiceLocator();
        $viewHelperManager = $services->get('ViewHelperManager');
        $helper = $viewHelperManager->get('mirador');
        $this->assertInstanceOf(Mirador::class, $helper);
    }

    public function testHelperExtendsAbstractHelper(): void
    {
        $helper = new Mirador();
        $this->assertInstanceOf(\Laminas\View\Helper\AbstractHelper::class, $helper);
    }

    public function testHelperIsInvokable(): void
    {
        $this->assertTrue(is_callable(new Mirador()));
    }

    public function testHelperReturnsEmptyStringForEmptyResource(): void
    {
        $services = $this->getServiceLocator();
        $viewHelperManager = $services->get('ViewHelperManager');
        $helper = $viewHelperManager->get('mirador');
        // Passing null/empty should return empty string.
        $this->assertEquals('', $helper(null));
        $this->assertEquals('', $helper([]));
    }

    public function testHelperAcceptsThemeInConstructor(): void
    {
        // Test constructor with null theme (no site context).
        $helper = new Mirador(null);
        $this->assertInstanceOf(Mirador::class, $helper);
    }
}
