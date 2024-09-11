<?php


/**
 * Tests for Tag Controller.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test class for the TagController.
 */
class TagControllerTest extends WebTestCase
{
    /**
     * Test the tag list page.
     *
     * @return void
     */
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tag');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tag list');
    }
}
