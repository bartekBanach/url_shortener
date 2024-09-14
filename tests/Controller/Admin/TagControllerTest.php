<?php

/**
 * Tests for Tag Controller.
 */

namespace App\Tests\Controller\Admin;

use App\Entity\Tag;
use App\Entity\User;
use App\Service\TagServiceInterface;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TagControllerTest.
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
     * Test client.
     */
    private KernelBrowser $httpClient;

    private TagServiceInterface $tagService;
    private TranslatorInterface $translator;
    private UserService $userService;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->tagService = static::getContainer()->get(TagServiceInterface::class);
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
     * Test show action for admin user.
     */
    public function testShowActionAdminUser(): void
    {
        $expectedStatusCode = 200;

        $adminUser = $this->createUser(['ROLE_ADMIN']);
        $tag = $this->createTag('Sample Tag');
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tag->getId());
        $result = $this->httpClient->getResponse();
        $resultStatusCode = $result->getStatusCode();

        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertSelectorTextContains('html h1', $this->translator->trans('title.tag_details'));
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
            'tag[title]' => 'New Tag',
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
        $tag = $this->createTag('Tag to Edit');
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tag->getId().'/edit');
        $form = $crawler->selectButton($this->translator->trans('action.edit'))->form([
            'tag[title]' => 'Updated Tag',
        ]);
        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $updatedTag = $this->tagService->findOneById($tag->getId());
        $this->assertEquals('Updated Tag', $updatedTag->getTitle());
    }

    /**
     * Test delete action.
     */
    public function testDeleteAction(): void
    {
        $adminUser = $this->createUser(['ROLE_ADMIN']);
        $tag = $this->createTag('Tag to Delete');
        $this->httpClient->loginUser($adminUser);

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$tag->getId().'/delete');
        $form = $crawler->selectButton($this->translator->trans('action.delete'))->form();
        $this->httpClient->submit($form);

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $deletedTag = $this->tagService->findOneById($tag->getId());
        $this->assertNull($deletedTag);
    }



    /**
     * Create a tag for testing.
     *
     * @param string $name Tag name
     *
     * @return Tag Tag entity
     */
    private function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->setTitle($name);
        $this->tagService->save($tag);

        return $tag;
    }

    /**
     * Create a user for testing.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
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
