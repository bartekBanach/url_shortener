<?php
/**
 * Home controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Click;
use App\Entity\Enum\UserRole;
use App\Entity\Url;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ClickServiceInterface;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class HomeControllerTest.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    private UrlServiceInterface $urlService;

    private ClickServiceInterface $clickService;
    private TranslatorInterface $translator;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->urlService = $this->createMock(UrlServiceInterface::class);
        $this->clickService = $this->createMock(ClickServiceInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->httpClient = static::createClient();
    }

    /**
     * Test main index route.
     *
     * @return void
     */
    public function testIndex()
    {
        // given
        $expectedStatusCode = 200;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // expect
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test rate limiting for anonymous users.
     *
     * @return void
     */
    public function testRateLimitingForAnonymousUser()
    {
        // given
        static::getContainer()->get('cache.global_clearer')->clearPool('cache.rate_limiter'); // Reset rate limiter before test
        $this->httpClient->followRedirects();
        $crawler = $this->httpClient->request('GET', '/');
        for ($i = 0; $i < 10; ++$i) {
            $form = $crawler->selectButton('Skróć URL')->form([
                'url_form[longUrl]' => 'https://example.com',
                'url_form[tags]' => 'testTag1, testTag2',
            ]);

            $this->httpClient->submit($form);
            $this->assertSelectorExists('.alert-success');
        }
        // when
        $form = $crawler->selectButton('Skróć URL')->form([
            'url_form[longUrl]' => 'https://example.com',
            'url_form[tags]' => 'testTag1, testTag2',
        ]);

        $this->httpClient->submit($form);
        $this->assertSelectorExists('.alert-danger');
    }

    /**
     * Test rate limiting for authenticated users.
     *
     * @return void
     */
    public function testRateLimitingForAuthenticatedUser()
    {
        // given
        $this->httpClient->followRedirects();

        $testUser = $this->createUser([UserRole::ROLE_USER->value]);

        $this->httpClient->loginUser($testUser);

        $crawler = $this->httpClient->request('GET', '/');

        for ($i = 0; $i < 1; ++$i) {
            $form = $crawler->selectButton('Skróć URL')->form([
                'url_form[longUrl]' => 'https://example.com',
                'url_form[tags]' => 'testTag1, testTag2',
            ]);

            $this->httpClient->submit($form);
            // $response = $this->httpClient->getResponse();
            // echo($response->getContent());
        }
        // when
        $form = $crawler->selectButton('Skróć URL')->form([
            'url_form[longUrl]' => 'https://example.com',
            'url_form[tags]' => 'testTag1, testTag2',
        ]);
        $this->httpClient->submit($form);

        // then
        $this->assertSelectorExists('.alert-success');
    }

    // test if author is set properly for logged and anonymous user

    /**
     * Test if redirect route redirects to long url if short url is present in db.
     */
    public function testRedirectToLongUrl(): void
    {
        // given
        $this->httpClient->followRedirects(false); // Disable following redirects

        $url = new Url();
        $url->setLongUrl('https://www.google.com');

        $this->urlService->expects($this->once())
            ->method('findOneByShortUrl')
            ->with('abcd123')
            ->willReturn($url);

        $this->clickService->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Click::class));

        static::getContainer()->set(UrlServiceInterface::class, $this->urlService);
        static::getContainer()->set(ClickServiceInterface::class, $this->clickService);

        // when
        $crawler = $this->httpClient->request('GET', '/abcd123');

        // then
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('https://www.google.com');
    }

    /**
     * Test redirect route if short url is not found in db.
     */
    public function testRedirectToLongUrlNotFound(): void
    {
        // given
        $this->httpClient->followRedirects(false);

        $this->urlService->expects($this->once())
            ->method('findOneByShortUrl')
            ->with('invalidCode')
            ->willReturn(null);

        static::getContainer()->set(UrlServiceInterface::class, $this->urlService);

        // when
        $this->httpClient->request('GET', '/invalidCode');

        // then
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }
}
