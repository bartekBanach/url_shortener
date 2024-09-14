<?php
/**
 * Tests for User Repository.
 */

namespace App\Tests\Repository;

use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserRepositoryTest.
 */
class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    private $userRepository;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->purgeDatabase();
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * Test queryAll method.
     *
     * @throws ORMException
     */
    public function testQueryAll(): void
    {
        // given
        $dataSetSize = 5;
        $users = [];

        for ($i = 0; $i < $dataSetSize; ++$i) {
            $user = $this->createUser('test'.$i.'@example.com', 'password'.$i);
            $users[] = $user;
        }

        // when
        $queryBuilder = $this->userRepository->queryAll();
        $result = $queryBuilder->getQuery()->getResult();

        // then
        $this->assertCount($dataSetSize, $result);
        foreach ($users as $user) {
            $this->assertContains($user, $result);
        }
    }

    /**
     * Test findOneById method.
     *
     * @throws ORMException
     */
    public function testFindOneById(): void
    {
        // given
        $user = $this->createUser('test@example.com', 'password');
        $userId = $user->getId();

        // when
        $result = $this->userRepository->findOneById($userId);

        // then
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user, $result);
    }

    /**
     * Test save method.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testSave(): void
    {
        // given
        $user = $this->createUser('test@example.com', 'password');

        // when
        $userId = $user->getId();
        $result = $this->entityManager->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter(':id', $userId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        // then
        $this->assertEquals($user, $result);
    }

    /**
     * Test delete method.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testDelete(): void
    {
        // given
        $user = $this->createUser('test@example.com', 'password');
        $userId = $user->getId();

        // when
        $this->userRepository->delete($user);

        // then
        $result = $this->entityManager->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter(':id', $userId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($result);
    }

    /**
     * Test delete method with cascading URLs.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testDeleteUserWithCascadingUrls(): void
    {
        // given
        $user = $this->createUser('test@example.com', 'password');

        $url1 = new Url();
        $url1->setLongUrl('https://example1.com');
        $url1->setAuthor($user);
        $this->entityManager->persist($url1);

        $url2 = new Url();
        $url2->setLongUrl('https://example2.com');
        $url2->setAuthor($user);
        $this->entityManager->persist($url2);

        $this->entityManager->flush();
        $this->assertCount(2, $this->entityManager->getRepository(Url::class)->findBy(['author' => $user]));
        $userId = $user->getId();

        // when
        $this->userRepository->delete($user);

        // then
        $deletedUser = $this->userRepository->findOneById($userId);
        $this->assertNull($deletedUser);
        $remainingUrls = $this->entityManager->getRepository(Url::class)->findBy(['author' => $user]);
        $this->assertCount(0, $remainingUrls);
    }

    /**
     * Test upgradePassword method.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testUpgradePassword(): void
    {
        // given
        $user = $this->createUser('test@example.com', 'oldpassword');

        // when
        $newHashedPassword = 'newhashedpassword';
        $this->userRepository->upgradePassword($user, $newHashedPassword);

        // then
        $updatedUser = $this->userRepository->findOneById($user->getId());
        $this->assertEquals($newHashedPassword, $updatedUser->getPassword());
    }

    /**
     * Purges the database before each test.
     */
    private function purgeDatabase(): void
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    /**
     * Helper function to create a user.
     *
     * @param string $email    the user's email
     * @param string $password the user's password
     *
     * @return User the created user entity
     */
    private function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
