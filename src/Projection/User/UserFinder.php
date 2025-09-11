<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Entity\User;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;
use Xm\SymfonyBundle\Model\Email;

/**
 * @extends ServiceEntityRepository<\App\Entity\User>
 *
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

    public function findOrThrow(UserId|string $id): User
    {
        $user = $this->find($id);
        if (!$user) {
            throw UserNotFound::withUserId($id);
        }

        return $user;
    }

    public function findRefreshed(UserId|string $id): ?User
    {
        $user = $this->find($id);

        if (!$user) {
            return null;
        }

        $this->getEntityManager()->refresh($user);

        return $user;
    }

    #[ArrayShape([User::class])]
    public function findByFilters(UserFilters $filters): array
    {
        $rsm = $this->createResultSetMappingBuilder('u');
        $select = $rsm->generateSelectClause();
        $queryParts = new UserFilterQueryBuilder()->queryParts($filters);

        $sql = <<<Query
SELECT {$select}
FROM `user` u
{$queryParts['join']}
WHERE {$queryParts['where']}
GROUP BY u.user_id
ORDER BY {$queryParts['order']}
LIMIT :offset, :maxResults
Query;

        if ($filters->applied(UserFilters::OFFSET)) {
            $queryParts['parameters']['offset'] = (int) $filters->get(UserFilters::OFFSET);
        } else {
            $queryParts['parameters']['offset'] = 0;
        }
        $queryParts['parameters']['maxResults'] = 30;

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters($queryParts['parameters']);

        return $query->getResult();
    }

    /**
     * Retrieve the total count based on filters.
     */
    public function countByFilters(UserFilters $filters): int
    {
        $queryParts = new UserFilterQueryBuilder()->queryParts($filters);

        $sql = <<<Query
SELECT COUNT(DISTINCT u.user_id)
FROM `user` u
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
