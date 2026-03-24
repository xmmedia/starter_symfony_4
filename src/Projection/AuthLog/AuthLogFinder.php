<?php

declare(strict_types=1);

namespace App\Projection\AuthLog;

use App\Entity\AuthLog;
use App\Entity\User;
use App\Model\AuthLog\AuthLogId;
use App\Model\User\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @extends ServiceEntityRepository<AuthLog>
 *
 * @method AuthLog|null find(AuthLogId|string $id, LockMode|int|null $lockMode = null, int|null $lockVersion = null)
 * @method AuthLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthLog[]    findAll()
 * @method AuthLog[]    findBy(array $criteria, array $orderBy = null, int|null $limit = null, int|null $offset = null)
 */
class AuthLogFinder extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthLog::class);
    }

    #[ArrayShape([AuthLog::class])]
    public function findByUserId(User|UserId $user, int $limit = 30, int $offset = 0): array
    {
        return $this->createQueryBuilder('a')
            ->where('IDENTITY(a.user) = :user OR IDENTITY(a.impersonatedUser) = :user')
            ->setParameter('user', $user)
            ->orderBy('a.occurredAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countByUserId(User|UserId $user): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.authLogId)')
            ->where('IDENTITY(a.user) = :user OR IDENTITY(a.impersonatedUser) = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    #[ArrayShape([AuthLog::class])]
    public function findByFilters(AuthLogFilters $filters): array
    {
        $rsm = $this->createResultSetMappingBuilder('a');
        $select = $rsm->generateSelectClause();
        $queryParts = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $sql = <<<Query
SELECT {$select}
FROM `auth_log` a
{$queryParts['join']}
WHERE {$queryParts['where']}
GROUP BY a.auth_log_id
ORDER BY {$queryParts['order']}
LIMIT :offset, :maxResults
Query;

        if ($filters->applied(AuthLogFilters::OFFSET)) {
            $queryParts['parameters']['offset'] = (int) $filters->get(AuthLogFilters::OFFSET);
        } else {
            $queryParts['parameters']['offset'] = 0;
        }
        $queryParts['parameters']['maxResults'] = 30;

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($queryParts['parameters']);

        return $query->getResult();
    }

    public function countByFilters(AuthLogFilters $filters): int
    {
        $queryParts = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $sql = <<<Query
SELECT COUNT(DISTINCT a.auth_log_id)
FROM `auth_log` a
{$queryParts['join']}
WHERE {$queryParts['where']}
Query;

        return (int) $this->getEntityManager()->getConnection()
            ->executeQuery(
                $sql,
                $queryParts['parameters'],
                $queryParts['parameterTypes'],
            )
            ->fetchNumeric()[0];
    }
}
