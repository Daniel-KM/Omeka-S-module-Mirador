<?php declare(strict_types=1);

namespace MiradorTest\Site\ResourcePageBlockLayout;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\Site\ResourcePageBlockLayout\Mirador;
use MiradorTest\MiradorTestTrait;
use Omeka\Site\ResourcePageBlockLayout\ResourcePageBlockLayoutInterface;

/**
 * Tests for the Mirador resource page block layout.
 */
class MiradorTest extends AbstractHttpControllerTestCase
{
    use MiradorTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function testResourcePageBlockLayoutIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $manager = $services->get('Omeka\ResourcePageBlockLayoutManager');
        $this->assertTrue($manager->has('mirador'));
    }

    public function testResourcePageBlockLayoutIsInstantiable(): void
    {
        $services = $this->getServiceLocator();
        $manager = $services->get('Omeka\ResourcePageBlockLayoutManager');
        $layout = $manager->get('mirador');
        $this->assertInstanceOf(Mirador::class, $layout);
    }

    public function testResourcePageBlockLayoutImplementsInterface(): void
    {
        $layout = new Mirador();
        $this->assertInstanceOf(ResourcePageBlockLayoutInterface::class, $layout);
    }

    public function testResourcePageBlockLayoutLabel(): void
    {
        $layout = new Mirador();
        $this->assertEquals('Mirador IIIF viewer', $layout->getLabel());
    }

    public function testCompatibleResourceNames(): void
    {
        $layout = new Mirador();
        $resourceNames = $layout->getCompatibleResourceNames();

        $this->assertContains('items', $resourceNames);
        $this->assertContains('media', $resourceNames);
        $this->assertContains('item_sets', $resourceNames);
        $this->assertCount(3, $resourceNames);
    }
}
