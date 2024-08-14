<?php
/**
 * Home controller tests.
 */

namespace App\Tests\Controller;

use App\Controller\HomeController;
use App\Entity\Url;
use App\Repository\UserRepository;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    private TranslatorInterface $translator;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->urlService = $this->createMock(UrlServiceInterface::class);
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
            $form = $crawler->selectButton('Shorten url')->form([
                'url_form[longUrl]' => 'https://example.com',
                'url_form[tags]' => 'testTag1, testTag2',
            ]);

            $this->httpClient->submit($form);
            $this->assertSelectorTextContains('.alert-success', 'Created successfully');
        }
        // when

        $form = $crawler->selectButton('Shorten url')->form([
            'url_form[longUrl]' => 'https://example.com',
            'url_form[tags]' => 'testTag1, testTag2',
        ]);

        $this->httpClient->submit($form);

        $this->assertSelectorTextContains('.alert-danger', 'Rate limit exceeded. Please try again later.');
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

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@example.com');

        $this->httpClient->loginUser($testUser);

        $crawler = $this->httpClient->request('GET', '/');

        for ($i = 0; $i < 10; ++$i) {
            $form = $crawler->selectButton('Shorten url')->form([
                'url_form[longUrl]' => 'https://example.com',
                'url_form[tags]' => 'testTag1, testTag2',
            ]);

            $this->httpClient->submit($form);
        }
        // when
        $form = $crawler->selectButton('Shorten url')->form([
            'url_form[longUrl]' => 'https://example.com',
            'url_form[tags]' => 'testTag1, testTag2',
        ]);
        $this->httpClient->submit($form);

        // then
        $this->assertSelectorTextContains('.alert-success', 'Created successfully');
    }


    /**
     * Test redirect when short url is present in db.
     *
     * @return void
     */
    public function testRedirectToLongUrl()
    {
        // given
        $url = new Url();
        $url->setLongUrl('https://www.google.com');

        $this->urlService->method('findUrlByShortUrl')->willReturn($url);

        $controller = new HomeController($this->urlService, $this->translator);
        $controller->setContainer(self::$kernel->getContainer());

        // when
        $response = $controller->redirectToLongUrl('shortCode');

        // then
        $this->assertTrue($response->isRedirect('https://www.google.com'));
    }

    /**
     * Test redirect if short url is not found in db.
     *
     * @return void
     */
    public function testRedirectToLongUrlNotFound()
    {
        // given
        $this->urlService->method('findUrlByShortUrl')->willReturn(null);

        $controller = new HomeController($this->urlService, $this->translator);
        $controller->setContainer(self::$kernel->getContainer());

        // when
        try {
            $response = $controller->redirectToLongUrl('invalidCode');
        } catch (NotFoundHttpException $e) {
            // then
            $this->assertEquals(Response::HTTP_NOT_FOUND, $e->getStatusCode());

            return;
        }


        $this->fail('Expected NotFoundHttpException was not thrown.');
    }
}
