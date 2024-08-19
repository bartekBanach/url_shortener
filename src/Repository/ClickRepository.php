<?php

namespace App\Repository;

use App\Entity\Click;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ClickRepository.
 *
 * @method Click|null find($id, $lockMode = null, $lockVersion = null)
 * @method Click|null findOneBy(array $criteria, array $orderBy = null)
 * @method Click[]    findAll()
 * @method Click[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Click>
 */
class ClickRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Click::class);
    }

    /**
     * Save click entity.
     *
     * @param Click $click Click entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Click $click): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($click);
        $this->_em->flush();
    }

    /**
     * Delete click entity.
     *
     * @param Click $click Click entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Click $click): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($click);
        $this->_em->flush();
    }

    /**
     * Query all clicks for a specific URL.
     *
     * @param Url $url URL entity
     *
     * @return QueryBuilder Query builder for clicks related to the URL
     */
    public function queryClicksByUrl(Url $url): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->andWhere('click.url = :url')
            ->setParameter('url', $url)
            ->orderBy('click.createdAt', 'DESC');
    }

    /**
     * Find all clicks by URL.
     *
     * @param Url $url URL entity
     *
     * @return Click[] List of clicks related to the URL
     */
    public function findClicksByUrl(Url $url): array
    {
        return $this->queryClicksByUrl($url)->getQuery()->getResult();
    }

    /**
     * Count clicks by URL.
     *
     * @param Url $url URL entity
     *
     * @return int The number of clicks related to the URL
     */
    public function countClicksByUrl(Url $url): int
    {
        return (int) $this->queryClicksByUrl($url)
            ->select('COUNT(click.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('click');
    }
}
