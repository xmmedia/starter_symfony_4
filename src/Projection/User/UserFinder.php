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
    public function findByUserFilters(UserFilters $filters): array
    {
        $qb = $this->createQueryBuilder('u')
            ->addOrderBy('u.email', \Doctrine\Common\Collections\Criteria::ASC)
            ->addOrderBy('u.firstName', \Doctrine\Common\Collections\Criteria::ASC)
            ->addOrderBy('u.lastName', \Doctrine\Common\Collections\Criteria::ASC);

        if ($filters->applied(UserFilters::EMAIL)) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%'.$filters->get(UserFilters::EMAIL).'%');
        }

        if ($filters->applied(UserFilters::EMAIL_EXACT)) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', $filters->get(UserFilters::EMAIL_EXACT));
        }

        if ($filters->applied(UserFilters::ACTIVE)) {
            $qb->andWhere('u.active = true')
                ->andWhere('u.verified = true');
        }

        if ($filters->applied(UserFilters::ROLES)) {
            $roleQueries = [];

            foreach ($filters->get(UserFilters::ROLES) as $i => $role) {
                $roleQueries[] = sprintf('JSON_CONTAINS(u.roles, :role%d) = 1', $i);
                $qb->setParameter('role'.$i, sprintf('"%s"', $role));
            }

            $qb->andWhere($qb->expr()->orX(...$roleQueries));
        }

        return $qb->getQuery()
            ->getResult();
    }
}
