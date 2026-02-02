<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Entity\User;
use App\Model\User\Exception\UserNotFound;
use App\Projection\User\UserFilters;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

class UserFinderTest extends BaseTestCase
{
    public function testFindOrThrow(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $user = \Mockery::mock(User::class);

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userId)
            ->andReturn($user);

        $result = $finder->findOrThrow($userId);

        $this->assertSame($user, $result);
    }

    public function testFindOrThrowNotFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userId)
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        $finder->findOrThrow($userId);
    }

    public function testFindOrThrowWithStringId(): void
    {
        $faker = $this->faker();
        $userIdString = $faker->uuid();
        $user = \Mockery::mock(User::class);

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userIdString)
            ->andReturn($user);

        $result = $finder->findOrThrow($userIdString);

        $this->assertSame($user, $result);
    }

    public function testFindRefreshed(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $user = \Mockery::mock(User::class);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('refresh')
            ->once()
            ->with($user);

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userId)
            ->andReturn($user);
        $finder->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $result = $finder->findRefreshed($userId);

        $this->assertSame($user, $result);
    }

    public function testFindRefreshedNotFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userId)
            ->andReturnNull();

        $result = $finder->findRefreshed($userId);

        $this->assertNull($result);
    }

    public function testFindByFilters(): void
    {
        $user = \Mockery::mock(User::class);
        $filters = UserFilters::fromArray([]);

        $rsm = \Mockery::mock(ResultSetMappingBuilder::class);
        $rsm->shouldReceive('generateSelectClause')
            ->once()
            ->andReturn('u.user_id, u.email');

        $query = \Mockery::mock(NativeQuery::class);
        $query->shouldReceive('setParameters')
            ->once()
            ->with(['offset' => 0, 'maxResults' => 30]);
        $query->shouldReceive('getResult')
            ->once()
            ->andReturn([$user]);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('createNativeQuery')
            ->once()
            ->with(
                \Mockery::on(
                    static fn ($sql): bool => str_contains($sql, 'SELECT')
                        && str_contains($sql, 'FROM `user` u')
                        && str_contains($sql, 'WHERE')
                        && str_contains($sql, 'ORDER BY'),
                ),
                $rsm,
            )
            ->andReturn($query);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('createResultSetMappingBuilder')
            ->once()
            ->with('u')
            ->andReturn($rsm);
        $finder->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $result = $finder->findByFilters($filters);

        $this->assertSame([$user], $result);
    }

    /**
     * Tests else case where no offset is applied.
     */
    public function testFindByFiltersNoOffset(): void
    {
        $user = \Mockery::mock(User::class);
        $filters = UserFilters::fromArray(['offset' => null]);

        $rsm = \Mockery::mock(ResultSetMappingBuilder::class);
        $rsm->shouldReceive('generateSelectClause')
            ->once()
            ->andReturn('u.user_id, u.email');

        $query = \Mockery::mock(NativeQuery::class);
        $query->shouldReceive('setParameters')
            ->once()
            ->with(['offset' => 0, 'maxResults' => 30]);
        $query->shouldReceive('getResult')
            ->once()
            ->andReturn([$user]);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('createNativeQuery')
            ->once()
            ->with(
                \Mockery::on(
                    static fn ($sql): bool => str_contains($sql, 'SELECT')
                        && str_contains($sql, 'FROM `user` u')
                        && str_contains($sql, 'WHERE')
                        && str_contains($sql, 'ORDER BY')
                        && !str_contains($sql, 'OFFSET'),
                ),
                $rsm,
            )
            ->andReturn($query);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('createResultSetMappingBuilder')
            ->once()
            ->with('u')
            ->andReturn($rsm);
        $finder->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $result = $finder->findByFilters($filters);

        $this->assertSame([$user], $result);
    }

    public function testCountByFilters(): void
    {
        $faker = $this->faker();

        $count = $faker->randomNumber();

        $filters = UserFilters::fromArray([]);

        $statement = \Mockery::mock(\Doctrine\DBAL\Result::class);
        $statement->shouldReceive('fetchNumeric')
            ->once()
            ->andReturn([$count]);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->with(
                \Mockery::on(static fn ($sql): bool => str_contains($sql, 'SELECT COUNT(DISTINCT u.user_id)')
                    && str_contains($sql, 'FROM `user` u')
                    && str_contains($sql, 'WHERE')),
                [],
                [],
            )
            ->andReturn($statement);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getConnection')
            ->once()
            ->andReturn($connection);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $finder = \Mockery::mock(UserFinder::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $finder->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $result = $finder->countByFilters($filters);

        $this->assertSame($count, $result);
    }
}
