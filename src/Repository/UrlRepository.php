<?php

namespace App\Repository;

use App\Dto\UrlListFiltersDto;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UrlRepository.
 *
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Url>
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
class UrlRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 3;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    /**
     * Query all records.
     *
     * @param UrlListFiltersDto $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(UrlListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->orderBy('url.createdAt', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Query URLs by author.
     *
     * @param User              $user    User entity
     * @param UrlListFiltersDto $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(User $user, UrlListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('url.author = :author')
            ->setParameter('author', $user);

        return $queryBuilder;
    }

    /**
     * Find by short url code.
     *
     * @param string $shortUrl Short url code
     *
     * @return Url|null Url entity
     */
    public function findOneByShortUrl(string $shortUrl): ?Url
    {
        return $this->findOneBy(['shortUrl' => $shortUrl]);
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($url);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Url $url): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($url);
        $this->_em->flush();
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
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }

    /**
     * Apply filters to paginated URL list.
     *
     * @param QueryBuilder      $queryBuilder Query builder
     * @param UrlListFiltersDto $filters      Filters
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, UrlListFiltersDto $filters): QueryBuilder
    {
        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere(':tag MEMBER OF url.tags')
                ->setParameter('tag', $filters->tag);
        }

        return $queryBuilder;
    }
}
