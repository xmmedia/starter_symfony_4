<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\AuthRepository;
use App\Model\Auth\Auth;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Util\Tests\AggregateRepositoryFactory;

class AuthRepositoryTest extends BaseTestCase
{
    use AggregateRepositoryFactory;

    public function testSaveGet(): void
    {
        $faker = $this->faker();

        $auth = Auth::success(
            $faker->authId(),
            $faker->userId(),
            $faker->emailVo(),
            $faker->userAgent(),
            $faker->ipv4(),
        );

        /** @var AuthRepository $repository */
        $repository = $this->getRepository(AuthRepository::class, Auth::class);

        $repository->save($auth);

        $fetchedAuth = $repository->get($auth->authId());

        $this->assertInstanceOf(Auth::class, $fetchedAuth);
        $this->assertNotSame($auth, $fetchedAuth);
        $this->assertSameValueAs($auth->authId(), $fetchedAuth->authId());
    }

    public function testGetDoesntExist(): void
    {
        $faker = $this->faker();

        /** @var AuthRepository $repository */
        $repository = $this->getRepository(AuthRepository::class, Auth::class);
        $fetched = $repository->get($faker->authId());

        $this->assertNull($fetched);
    }
}
