<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Entity\User;
use App\Model\User\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xm\SymfonyBundle\Model\Email;

/**
 * @method User|null find(UserId|string $id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method User|null findOneByEmail(Email $email, array $orderBy = null)
 */
class UserFinder extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findByFilters(UserFilters $filters): array
    {
        $rsm = $this->createResultSetMappingBuilder('u');
        $select = $rsm->generateSelectClause();
        $queryParts = (new UserFilterQueryBuilder())->queryParts($filters);

        $sql = <<<Query
SELECT {$select}
FROM `user` u
{$queryParts['join']}
WHERE {$queryParts['where']}
GROUP BY u.user_id
ORDER BY {$queryParts['order']}
Query;

        $sql .= ' LIMIT :offset, :maxResults';

        if ($filters->applied(UserFilters::OFFSET)) {
            $queryParts['parameters']['offset'] = (int) $filters->get(UserFilters::OFFSET);
        } else {
            $queryParts['parameters']['offset'] = 0;
        }
        $queryParts['parameters']['maxResults'] = 30;

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters($queryParts['parameters']);

        return $query->getResult();
    }

    /**
     * Retrieve the total count based on filters.
     */
    public function countByFilters(UserFilters $filters): int
    {
        $queryParts = (new UserFilterQueryBuilder())->queryParts($filters);

        $sql = <<<Query
SELECT COUNT(DISTINCT u.user_id)
FROM `user` u
{$queryParts['join']}
WHERE {$queryParts['where']}
Query;

        return (int) $this->_em->getConnection()
            ->executeQuery(
                $sql,
                $queryParts['parameters'],
                $queryParts['parameterTypes'],
            )
            ->fetchNumeric()[0];
    }
}
