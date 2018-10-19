<?php

namespace Tests;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait UserAuthMock
{
    /**
     * Creates a user mock with specified ID.
     *
     * @param int $id
     * @param int $getIdTimes
     *
     * @return \Mockery\MockInterface
     */
    protected function getUserMock($id, $getIdTimes = 1)
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getId')
            ->times($getIdTimes)
            ->andReturn($id);

        return $user;
    }

    /**
     * Creates a token interface mock with user.
     * The user can be a user ID or a user mock.
     *
     * @param int|\Mockery\MockInterface $user
     * @param int                        $getUserTimes
     *
     * @return \Mockery\MockInterface
     */
    protected function getTokenMock($user, $getUserTimes = 1)
    {
        if (!$user instanceof \Mockery\MockInterface) {
            // use the $user var as the user ID
            $user = $this->getUserMock($user);
        }

        $token = \Mockery::mock(TokenInterface::class);
        $token->shouldReceive('getUser')
            ->times($getUserTimes)
            ->andReturn($user);

        return $token;
    }
}