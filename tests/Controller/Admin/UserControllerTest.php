<?php

/**
 * Tests for User Controller.
 */

namespace App\Tests\Controller\Admin;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Service\UrlService;
use App\Service\UserService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/admin/user';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    private UserService $userService;
    private UrlService $urlService;
    private TranslatorInterface $translator;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->userService = static::getContainer()->get(UserService::class);
        $this->urlService = static::getContainer()->get(UrlService::class);

        $this->translator = static::getContainer()->get('translator');
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        $expectedStatusCode = 302;

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show action for an admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowActionAdminUser(): void
    {
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);

        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$user->getId());
        $result = $this->httpClient->getResponse();
        $resultStatusCode = $result->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertSelectorTextContains('html h1', '#'.$user->getId());
    }

    /**
     * Test create action with form submission.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateAction(): void
    {
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        $form = $crawler->selectButton($this->translator->trans('action.save'))->form([
            'user_form[email]' => 'newuser@example.com',
            'user_form[password]' => 'newpassword123',
            'user_form[isVerified]' => true,
        ]);

        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * Test edit action.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditAction(): void
    {
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$user->getId().'/edit');
        $form = $crawler->selectButton($this->translator->trans('action.edit'))->form();
        $form['user_form[roles][0]']->tick();
        $form->setValues([
            'user_form[email]' => 'updated_user@example.com',
            'user_form[password]' => 'new_password',
        ]);
        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $updatedUser = $this->userService->findOneById($user->getId());
        $this->assertEquals('updated_user@example.com', $updatedUser->getEmail());
    }

    /**
     * Test delete action.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteAction(): void
    {
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $user = $this->createUser([UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$user->getId().'/delete');
        $form = $crawler->selectButton($this->translator->trans('action.delete'))->form();
        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');


        $deletedUser = $this->userService->findOneById($user->getId());
        $this->assertNull($deletedUser);
    }

    /**
     * Create a user for testing.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
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
