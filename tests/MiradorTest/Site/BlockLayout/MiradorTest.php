<?php declare(strict_types=1);

namespace MiradorTest\Site\BlockLayout;

use CommonTest\AbstractHttpControllerTestCase;
use Mirador\Site\BlockLayout\Mirador;
use MiradorTest\MiradorTestTrait;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Site\BlockLayout\TemplateableBlockLayoutInterface;

/**
 * Tests for the Mirador block layout.
 */
class MiradorTest extends AbstractHttpControllerTestCase
{
    use MiradorTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function testBlockLayoutIsRegistered(): void
    {
        $services = $this->getServiceLocator();
        $blockLayoutManager = $services->get('Omeka\BlockLayoutManager');
        $this->assertTrue($blockLayoutManager->has('mirador'));
    }

    public function testBlockLayoutIsInstantiable(): void
    {
        $services = $this->getServiceLocator();
        $blockLayoutManager = $services->get('Omeka\BlockLayoutManager');
        $layout = $blockLayoutManager->get('mirador');
        $this->assertInstanceOf(Mirador::class, $layout);
    }

    public function testBlockLayoutExtendsAbstractBlockLayout(): void
    {
        $layout = new Mirador();
        $this->assertInstanceOf(AbstractBlockLayout::class, $layout);
    }

    public function testBlockLayoutImplementsTemplateableInterface(): void
    {
        $layout = new Mirador();
        $this->assertInstanceOf(TemplateableBlockLayoutInterface::class, $layout);
    }

    public function testBlockLayoutLabel(): void
    {
        $layout = new Mirador();
        $this->assertEquals('Mirador Viewer', $layout->getLabel());
    }

    public function testDefaultPartialName(): void
    {
        $this->assertEquals('common/block-layout/mirador', Mirador::PARTIAL_NAME);
    }
}
