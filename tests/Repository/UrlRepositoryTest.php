<?php
/**
 * Tests for Url Repository.
 */

namespace App\Tests\Repository;

use App\Dto\UrlListFiltersDto;
use App\Entity\Url;
use App\Entity\User;
use App\Repository\UrlRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests for Url Repository.
 */
class UrlRepositoryTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    private UrlRepository $urlRepository;

    /**
     * This method is called before each test.
     *
     * @throws NotSupported
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // $this->urlRepository = $this->entityManager->getRepository(Url::class);
        $this->urlRepository = $this->entityManager->getRepository(Url::class);
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
        $dataSetSize = 10;
        $urls = [];

        for ($i = 0; $i < $dataSetSize; ++$i) {
            $url = new Url();
            $url->setLongUrl('https://example'.$i.'.com');
            $url->setShortUrl('abcdef'.$i);
            $url->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($url);
            $urls[] = $url;
        }

        $this->entityManager->flush();
        $filters = new UrlListFiltersDto(null);

        // when
        $queryBuilder = $this->urlRepository->queryAll($filters);
        $result = $queryBuilder->getQuery()->getResult();

        // then
        $this->assertCount($dataSetSize, $result);
        foreach ($urls as $url) {
            $this->assertContains($url, $result);
        }
    }

    /**
     * Test queryByAuthor with multiple records.
     *
     * @throws ORMException
     */
    public function testQueryByAuthor(): void
    {
        // given
        $author = new User();
        $author->setEmail('test_user@gmail.com');
        $author->setPassword('abc1234');
        $this->entityManager->persist($author);

        $dataSetSize = 10;
        $authorUrls = [];
        $otherUrls = [];

        for ($i = 0; $i < $dataSetSize; ++$i) {
            $url = new Url();
            $url->setLongUrl('https://example'.$i.'.com');
            $url->setShortUrl('abcdef'.$i);
            $url->setCreatedAt(new \DateTimeImmutable()); // Set to current date and time

            if (0 === $i % 2) {
                $url->setAuthor($author);
                $authorUrls[] = $url;
            } else {
                $otherUrls[] = $url;
            }

            $this->entityManager->persist($url);
        }

        $this->entityManager->flush();

        // when
        $filters = new UrlListFiltersDto(null);
        $queryBuilder = $this->urlRepository->queryByAuthor($author, $filters);
        $result = $queryBuilder->getQuery()->getResult();

        // then
        $this->assertCount(count($authorUrls), $result);
        foreach ($authorUrls as $url) {
            $this->assertContains($url, $result);
        }
        foreach ($otherUrls as $url) {
            $this->assertNotContains($url, $result);
        }
    }

    /**
     * Test findIOneByShortUrl.
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testFindOneByShortUrl(): void
    {
        // given
        $url = new Url();
        $url->setLongUrl('https://google.com');
        $url->setShortUrl('abc1234');
        $url->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($url);
        $this->entityManager->flush();

        // when
        $result = $this->urlRepository->findOneByShortUrl($url->getShortUrl());

        // then
        $this->assertInstanceOf(Url::class, $result);
        $this->assertEquals($url, $result);
    }

    /**
     * Test save method.
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function testSave(): void
    {
        // given
        $url = new Url();
        $url->setLongUrl('https://google.com');
        $url->setShortUrl('abc1234');
        $url->setCreatedAt(new \DateTimeImmutable());

        // when
        $this->urlRepository->save($url);

        // then
        $urlId = $url->getId();
        $result = $this->entityManager->createQueryBuilder()
            ->select('url')
            ->from(Url::class, 'url')
            ->where('url.id = :id')
            ->setParameter(':id', $urlId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($url, $result);
    }

    /**
     * Test delete method.
     *
     * @throws OptimisticLockException|ORMException
     */
    public function testDelete(): void
    {
        // given
        $url = new Url();
        $url->setLongUrl('https://google.com');
        $url->setShortUrl('abc1234');
        $url->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($url);
        $this->entityManager->flush();
        $urlId = $url->getId();

        // when
        $this->urlRepository->delete($url);

        // then
        $result = $this->entityManager->createQueryBuilder()
            ->select('url')
            ->from(Url::class, 'url')
            ->where('url.id = :id')
            ->setParameter(':id', $urlId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($result);
    }
}
