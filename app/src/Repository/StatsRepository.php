<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Stats;
use App\Model\MostFrequentRequest;
use App\Query\FizzBuzzQuery;
use App\Repository\Interface\StatsRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stats>
 */
class StatsRepository extends ServiceEntityRepository implements StatsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stats::class);
    }

    public function increment(FizzBuzzQuery $query): void
    {
        $this->getEntityManager()->getConnection()->executeStatement(
            'INSERT INTO stats (query, hits) VALUES (:query, 1) ON DUPLICATE KEY UPDATE hits = hits + 1',
            ['query' => $query->toStatsKey()],
        );
    }

    public function findMostFrequent(): ?MostFrequentRequest
    {
        return $this->createQueryBuilder('s')
            ->select(sprintf('NEW %s(s.query, s.hits)', MostFrequentRequest::class))
            ->orderBy('s.hits', 'DESC')
            ->addOrderBy('s.id', 'ASC') // résultat stable en cas d'égalité
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
