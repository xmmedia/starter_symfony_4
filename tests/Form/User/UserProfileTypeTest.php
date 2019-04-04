<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Entity\User;
use App\Form\User\UserProfileType;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserId;
use App\Tests\TypeTestCase;
use App\Validator\Constraints\UniqueCurrentUsersEmailValidator;
use Mockery;
use Symfony\Component\Security\Core\Security;

class UserProfileTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $faker = $this->faker();
        $userId = UserId::fromString($faker->uuid);

        $checker = Mockery::mock(ChecksUniqueUsersEmail::class);
        $checker->shouldReceive('__invoke')
            ->andReturn($userId);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($userId);

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $this->validatorContainer->set(
            UniqueCurrentUsersEmailValidator::class,
            new UniqueCurrentUsersEmailValidator($checker, $security)
        );
    }

    public function test()
    {
        $faker = $this->faker();

        $formData = [
            'email'     => $faker->email,
            'firstName' => $faker->name,
            'lastName'  => $faker->name,
        ];

        $form = $this->factory->create(UserProfileType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
