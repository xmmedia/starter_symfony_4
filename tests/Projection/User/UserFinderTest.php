<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Entity\User;
use App\Model\User\Exception\UserNotFound;
use App\Projection\User\UserFilterQueryBuilder;
use App\Projection\User\UserFilters;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NativeQuery;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class UserFinderTest extends BaseTestCase
{
    public function testFindOrThrowReturnsUserWhenFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $user = \Mockery::mock(User::class);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userId)
            ->andReturn($user);

        $result = $finder->findOrThrow($userId);

        $this->assertSame($user, $result);
    }

    public function testFindOrThrowThrowsExceptionWhenNotFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
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

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userIdString)
            ->andReturn($user);

        $result = $finder->findOrThrow($userIdString);

        $this->assertSame($user, $result);
    }

    public function testFindRefreshedReturnsNullWhenNotFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('find')
            ->once()
            ->with($userId)
            ->andReturnNull();

        $result = $finder->findRefreshed($userId);

        $this->assertNull($result);
    }

    public function testFindRefreshedRefreshesEntityWhenFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $user = \Mockery::mock(User::class);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));
        $entityManager->shouldReceive('refresh')
            ->once()
            ->with($user);

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
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

    public function testFindByFilters(): void
    {
        $user = \Mockery::mock(User::class);
        $filters = \Mockery::mock(UserFilters::class);
        $filters->shouldReceive('applied')->andReturn(true);
        $filters->shouldReceive('get')->andReturn(null);

        $queryParts = [
            'join' => 'LEFT JOIN foo f ON f.user_id = u.user_id',
            'where' => '1 = 1',
            'order' => 'u.user_id DESC',
            'parameters' => []
        ];

        // Ensure any new UserFilterQueryBuilder() inside the method returns our mock
        $builderMock = \Mockery::mock('overload:' . UserFilterQueryBuilder::class);
        $builderMock->shouldReceive('queryParts')
            ->once()
            ->with($filters)
            ->andReturn($queryParts);

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
            ->with(\Mockery::on(fn($sql): bool => str_contains($sql, 'FROM `user` u')
                && str_contains($sql, $queryParts['where'])
                && str_contains($sql, $queryParts['order'])), $rsm)
            ->andReturn($query);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
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
    public function testFindByFiltersNoOffsetApplied(): void
    {
        $user = \Mockery::mock(User::class);
        $filters = \Mockery::mock(UserFilters::class);
        $filters->shouldReceive('applied')->with(UserFilters::OFFSET)->andReturn(false);

        $queryParts = [
            'join'       => '',
            'where'      => '1 = 1',
            'order'      => 'u.user_id DESC',
            'parameters' => []
        ];

        // Ensure any new UserFilterQueryBuilder() inside the method returns our mock
        $builderMock = \Mockery::mock('overload:' . UserFilterQueryBuilder::class);
        $builderMock->shouldReceive('queryParts')
            ->once()
            ->with($filters)
            ->andReturn($queryParts);

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
            ->with(\Mockery::on(fn($sql): bool => str_contains($sql, 'FROM `user` u')
                && str_contains($sql, $queryParts['where'])
                && str_contains($sql, $queryParts['order'])), $rsm)
            ->andReturn($query);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
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

        $filters = \Mockery::mock(UserFilters::class);

        $queryParts = [
            'join' => 'LEFT JOIN foo f ON f.user_id = u.user_id',
            'where' => '1 = 1',
            'parameters' => ['param1' => 'value1'],
            'parameterTypes' => ['param1' => 'string']
        ];

        $builderMock = \Mockery::mock('overload:' . UserFilterQueryBuilder::class);
        $builderMock->shouldReceive('queryParts')
            ->once()
            ->with($filters)
            ->andReturn($queryParts);

        $statement = \Mockery::mock(\Doctrine\DBAL\Result::class);
        $statement->shouldReceive('fetchNumeric')
            ->once()
            ->andReturn([$count]);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->with(\Mockery::on(fn($sql): bool => str_contains($sql, 'SELECT COUNT(DISTINCT u.user_id)')
                && str_contains($sql, 'FROM `user` u')
                && str_contains($sql, $queryParts['join'])
                && str_contains($sql, $queryParts['where'])),
                $queryParts['parameters'],
                $queryParts['parameterTypes']
            )
            ->andReturn($statement);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getConnection')
            ->once()
            ->andReturn($connection);
        $entityManager->shouldReceive('getClassMetadata')
            ->with(User::class)
            ->andReturn(\Mockery::mock(ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(User::class)
            ->andReturn($entityManager);

        $finder = \Mockery::mock(UserFinder::class, [$registry])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $finder->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $result = $finder->countByFilters($filters);

        $this->assertSame($count, $result);
    }
}
