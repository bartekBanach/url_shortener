<?php

/**
* Tests for Url Controller.
*/

namespace App\Tests\Controller\Admin;

use App\Entity\Url;
use App\Entity\User;
use App\Service\UrlServiceInterface;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
* Class UrlControllerTest.
*/
class UrlControllerTest extends WebTestCase
{
/**
* Test route.
*
* @const string
*/
    public const TEST_ROUTE = '/admin/url';

/**
* Test client.
*/
    private KernelBrowser $httpClient;

    private UrlServiceInterface $urlService;
    private TranslatorInterface $translator;
    private UserService $userService;

/**
* Set up tests.
*/
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->urlService = static::getContainer()->get(UrlServiceInterface::class);
        $this->userService = static::getContainer()->get(UserService::class);
        $this->translator = static::getContainer()->get('translator');
    }

/**
* Test index route for admin user.
*/
    public function testIndexRouteAdminUser(): void
    {
        $expectedStatusCode = 200;

        $adminUser = $this->createUser(['ROLE_ADMIN']);
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

/**
* Test index route for regular user.
*/
    public function testIndexRouteRegularUser(): void
    {
        $expectedStatusCode = 403;

        $regularUser = $this->createUser(['ROLE_USER']);
        $this->httpClient->loginUser($regularUser);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
    * Test show action.
    */
    public function testShowAction(): void
    {
        $expectedStatusCode = 200;

        $adminUser = $this->createUser(['ROLE_ADMIN']);
        $url = $this->createUrl();
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$url->getId());
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertSelectorTextContains('html h1', $this->translator->trans('title.url_details'));
    }

    /**
    * Test create action with form submission.
    */
    public function testCreateAction(): void
    {
        $adminUser = $this->createUser(['ROLE_ADMIN']);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        $form = $crawler->selectButton($this->translator->trans('action.save'))->form([
            'url_form[longUrl]' => 'https://example.com',
            'url_form[tags]' => 'tag1, tag2',
        ]);

        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

/**
* Test edit action.
*/
    public function testEditAction(): void
    {
        $adminUser = $this->createUser(['ROLE_ADMIN']);
        $url = $this->createUrl();
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$url->getId().'/edit');
        $form = $crawler->selectButton($this->translator->trans('action.edit'))->form([
            'url_form[longUrl]' => 'https://updated-url.com',
            'url_form[tags]' => 'tag1, tag2',
        ]);
        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $updatedUrl = $this->urlService->findOneById($url->getId());
        $this->assertEquals('https://updated-url.com', $updatedUrl->getLongUrl());
    }

/**
* Test delete action.
*/
    public function testDeleteAction(): void
    {
        $adminUser = $this->createUser(['ROLE_ADMIN']);
        $url = $this->createUrl();
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$url->getId().'/delete');
        $form = $crawler->selectButton($this->translator->trans('action.delete'))->form();
        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $deletedUrl = $this->urlService->findOneById($url->getId());
        $this->assertNull($deletedUrl);
    }

/**
* Create a URL for testing.
*
* @return Url URL entity
*/
    private function createUrl(): Url
    {
        $url = new Url();
        $url->setLongUrl('https://example.com');
        $this->urlService->save($url);

        return $url;
    }

/**
* Create a user for testing.
*
* @param array $roles User roles
*
* @return User User entity
*/
    private function createUser(array $roles): User
    {
        $user = new User();
        $user->setEmail('user_'.uniqid().'@example.com');
        $user->setRoles($roles);
        $user->setPassword('p@55w0rd');

        $this->userService->save($user);

        return $user;
    }
}
