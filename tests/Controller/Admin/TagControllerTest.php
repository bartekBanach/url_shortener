<?php


/**
 * Tests for Tag Controller.
 */

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test class for the TagController.
 */
class TagControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/admin/tag';

    /**
     * Test the tag list page.
     *
     * @return void
     */
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', self::TEST_ROUTE);

        $this->assertResponseIsSuccessful();
    }
}
