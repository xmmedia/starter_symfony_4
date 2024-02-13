<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Controller\SecurityController;
use App\Entity\User;
use App\Model\User\Name;
use App\Security\Security;
use App\Tests\EmptyProvider;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;

trait UserMockForUserMutationTrait
{
    use EmptyProvider;

    private function getUserMock(): User|\Mockery\MockInterface
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('email')
            ->once()
            ->andReturn($faker->emailVo());
        $user->shouldReceive('firstName')
            ->once()
            ->andReturn(Name::fromString($faker->firstName()));
        $user->shouldReceive('lastName')
            ->once()
            ->andReturn(Name::fromString($faker->lastName()));

        return $user;
    }

    private function createSecurity(bool $isGrantedResult): Security|\Mockery\MockInterface
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isGranted')
            ->once()
            ->andReturn($isGrantedResult);

        return $security;
    }

    private function getResetPasswordHelper(User $user, bool $successful = true): ResetPasswordHelperInterface|\Mockery\MockInterface
    {
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn($user);
        if ($successful) {
            $resetPasswordHelper->shouldReceive('removeResetRequest')
                ->once()
                ->with(\Mockery::type('string'));
        }

        return $resetPasswordHelper;
    }

    private function getRequestInfoProvider(bool $successful = true): RequestInfoProvider|\Mockery\MockInterface
    {
        $session = \Mockery::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with(SecurityController::TOKEN_SESSION_KEY)
            ->andReturn($this->faker()->password());
        if ($successful) {
            $session->shouldReceive('remove')
                ->once()
                ->with(SecurityController::TOKEN_SESSION_KEY);
        }

        $request = \Mockery::mock(\Symfony\Component\HttpFoundation\Request::class);
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $requestProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        return $requestProvider;
    }
}
