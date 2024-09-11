<?php
/**
 * Tests for User Service.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * UserServiceTest.
 *
 * Tests for the UserService class.
 */
class UserServiceTest extends TestCase
{
    private UserRepository $userRepository;
    private PaginatorInterface $paginator;
    private UserPasswordHasherInterface $passwordHasher;
    private UserService $userService;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userService = new UserService(
            $this->userRepository,
            $this->paginator,
            $this->passwordHasher
        );
    }

    /**
     * Test getPaginatedList method.
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $pagination = $this->createMock(PaginationInterface::class);
        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with($this->isInstanceOf(QueryBuilder::class), $page, 10)
            ->willReturn($pagination);

        // when
        $result = $this->userService->getPaginatedList($page);

        // then
        $this->assertSame($pagination, $result);
    }

    /**
     * Test save method with password hashing.
     */
    public function testSaveWithPasswordHashing(): void
    {
        // given
        $user = new User();
        $user->setPassword('plain_password');
        $hashedPassword = 'hashed_password';
        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        // when
        $this->userService->save($user);

        // then
        $this->assertEquals($hashedPassword, $user->getPassword());
    }

    /**
     * Test delete method.
     */
    public function testDelete(): void
    {
        // given
        $user = new User();

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($user);

        // when
        $this->userService->delete($user);

        // then
    }

    /**
     * Test findOneById method.
     *
     * @throws NonUniqueResultException
     */
    public function testFindOneById(): void
    {
        // given
        $userId = 1;
        $user = new User();
        $this->userRepository->expects($this->once())
            ->method('findOneById')
            ->with($userId)
            ->willReturn($user);

        // when
        $result = $this->userService->findOneById($userId);

        // then
        $this->assertSame($user, $result);
    }
}
