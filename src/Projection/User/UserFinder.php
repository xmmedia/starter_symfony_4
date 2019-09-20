<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Entity\User;
use App\Model\Email;
use App\Model\User\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find(UserId|string $id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method User|null findOneByEmail(Email $email, array $orderBy = null)
 */
class UserFinder extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findByUserFilters(UserFilters $filters): array
    {
        $qb = $this->createQueryBuilder('u')
            ->addOrderBy('u.email', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->addOrderBy('u.lastName', 'ASC');

        if ($filters->applied(UserFilters::EMAIL)) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%'.$filters->get(UserFilters::EMAIL).'%');
        }

        if ($filters->applied(UserFilters::ACTIVE)) {
            $qb->andWhere('u.active = true')
                ->andWhere('u.verified = true');
        }

        return $qb->getQuery()
            ->getResult();
    }
}
