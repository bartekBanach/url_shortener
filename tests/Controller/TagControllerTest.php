<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tag');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tag list');
    }

    /**
     * Test rate limiting for anonymous users.
     *
     * @return void
     */
    /*
    public function testRateLimitingForAnonymousUser()
    {
        // given
        $client = static::createClient();
        static::getContainer()->get('cache.global_clearer')->clearPool('cache.rate_limiter'); // Reset rate limiter before test
        $client->followRedirects();
        $crawler = $client->request('GET', '/');

        for ($i = 0; $i < 10; ++$i) {
            $form = $crawler->selectButton('Shorten url')->form([
                'url_form[longUrl]' => 'https://example.com',
                'url_form[tags]' => 'testTag1, testTag2',
            ]);

            $client->submit($form);
            $this->assertSelectorTextContains('.alert-success', 'Created successfully');
        }
        // when

        $form = $crawler->selectButton('Shorten url')->form([
            'url_form[longUrl]' => 'https://example.com',
            'url_form[tags]' => 'testTag1, testTag2',
        ]);

        $client->submit($form);

        $this->assertSelectorTextContains('.alert-danger', 'Rate limit exceeded. Please try again later.');
    }

    /**
     * Test rate limiting for authenticated users.
     *
     * @return void
     */
    /*
    public function testRateLimitingForAuthenticatedUser()
    {
        // given
        $client = static::createClient();
        $client->followRedirects();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@example.com');

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/');

        for ($i = 0; $i < 10; ++$i) {
            $form = $crawler->selectButton('Shorten url')->form([
                'url_form[longUrl]' => 'https://example.com',
                'url_form[tags]' => 'testTag1, testTag2',
            ]);

            $client->submit($form);
        }
        // when
        $form = $crawler->selectButton('Shorten url')->form([
            'url_form[longUrl]' => 'https://example.com',
            'url_form[tags]' => 'testTag1, testTag2',
        ]);
        $client->submit($form);

        // then
        $this->assertSelectorTextContains('.alert-success', 'Created successfully');
    }*/
}
