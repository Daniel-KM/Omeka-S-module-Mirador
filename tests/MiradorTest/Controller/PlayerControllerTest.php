<?php declare(strict_types=1);

namespace MiradorTest\Controller;

use CommonTest\AbstractHttpControllerTestCase;
use MiradorTest\MiradorTestTrait;

/**
 * Tests for the Mirador player controller.
 */
class PlayerControllerTest extends AbstractHttpControllerTestCase
{
    use MiradorTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    /**
     * Test that the legacy mirador item route matches.
     */
    public function testMiradorItemRouteExists(): void
    {
        $this->dispatch('/item/1/mirador');
        // The route resolves the controller alias, not the canonical name.
        $this->assertControllerName('Mirador\Controller\Item');
        $this->assertActionName('play');
    }

    /**
     * Test that accessing a non-existent resource returns 404.
     */
    public function testPlayActionReturns404ForInvalidResource(): void
    {
        $this->dispatch('/item/999999/mirador');
        $this->assertResponseStatusCode(404);
    }

    /**
     * Test that the item-set route also matches the player controller.
     */
    public function testItemSetRouteMatchesPlayerController(): void
    {
        $this->dispatch('/item-set/1/mirador');
        // The route resolves the controller alias, not the canonical name.
        $this->assertControllerName('Mirador\Controller\ItemSet');
        $this->assertActionName('play');
    }

    /**
     * Test that the controller is publicly accessible (ACL allows null role).
     */
    public function testMiradorRouteIsPubliclyAccessible(): void
    {
        $this->dispatchUnauthenticated('/item/999999/mirador');
        // Should get 404 (resource not found), not 403 (forbidden).
        $this->assertResponseStatusCode(404);
    }
}
