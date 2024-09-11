<?php

/**
 * Url controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Url;
use App\Entity\User;
use App\Service\UrlService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test the UrlController functionality.
 */
class UrlControllerTest extends WebTestCase
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client HTTP client for making requests */
    private $httpClient;

    /** @var UrlService Service for URL-related operations */
    private UrlService $urlService;

    /**
     * Set up the test environment.
     * Initializes the HTTP client and URL service.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = static::createClient();
        $this->httpClient->followRedirects(true);
        $this->urlService = static::getContainer()->get(UrlService::class);
    }


    /**
     * Test that an author can view the URL details.
     *
     * Simulates a logged-in user who is the author of the URL and verifies that
     * the URL details page is accessible and displays the correct information.
     */
    public function testShowActionAsAuthor(): void
    {
        $user = $this->createUser();
        $url = $this->createUrl($user);

        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', sprintf('/url/%d', $url->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'URL Details');
    }

    /**
     * Test that a non-author cannot view the URL details and is redirected with a warning.
     *
     * Simulates a logged-in user who is not the author of the URL and verifies
     * that the user is redirected and sees a warning message.
     */
    public function testShowActionAsNonAuthor(): void
    {
        $user = $this->createUser();
        $url = $this->createUrl(null);

        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', sprintf('/url/%d', $url->getId()));
        $this->assertResponseStatusCodeSame(403); // Assert that the response status code is 403 Forbidden
    }


    /**
     * Create a User entity with unique email and predefined roles and password.
     *
     * @return User The created User entity
     */
    private function createUser(): User
    {
        $user = new User();
        $uniqueEmail = sprintf('user%s@example.com', uniqid('', true));
        $user->setEmail($uniqueEmail);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('password123');

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }


    /**
     * Create a Url entity with an optional author.
     *
     * @param User|null $author The user to set as the author, or null if no author
     *
     * @return Url The created Url entity
     */
    private function createUrl(?User $author): Url
    {
        $url = new Url();

        if (null !== $author) {
            $url->setAuthor($author);
        }
        $url->setLongUrl('https://www.instagram.com');

        $this->urlService->save($url);

        return $url;
    }
}
