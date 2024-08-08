<?php
/**
 * Home controller tests.
 */

namespace App\Tests\Controller;

use App\Controller\HomeController;
use App\Entity\Url;
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

    // If no code return main page
    // If code valid, redirect to longUrl
    // If code invalid, show error
    // else if code is one of the routes, go to route normally

    /**
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

        /*$this->expectException(NotFoundHttpException::class);

        $controller->redirectToLongUrl('invalidCode');*/

        $this->fail('Expected NotFoundHttpException was not thrown.');
    }
}
