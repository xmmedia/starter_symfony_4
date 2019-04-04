<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\AdminUserUpdateType;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Tests\TypeTestCase;
use App\Validator\Constraints\UniqueExistingUserEmailValidator;
use Mockery;

class AdminUserUpdateTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $checker = Mockery::mock(ChecksUniqueUsersEmail::class);
        $checker->shouldReceive('__invoke')
            ->andReturnNull();

        $this->validatorContainer->set(
            UniqueExistingUserEmailValidator::class,
            new UniqueExistingUserEmailValidator($checker)
        );
    }

    public function test()
    {
        $faker = $this->faker();

        $formData = [
            'userId'         => $faker->uuid,
            'changePassword' => true,
            'password'       => $faker->password,
            'email'          => $faker->email,
            'firstName'      => $faker->name,
            'lastName'       => $faker->name,
            'role'           => 'ROLE_USER',
        ];

        $form = $this->factory->create(AdminUserUpdateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }

    public function testNoPassword()
    {
        $faker = $this->faker();

        $formData = [
            'userId'         => $faker->uuid,
            'email'          => $faker->email,
            'changePassword' => false,
            'firstName'      => $faker->name,
            'lastName'       => $faker->name,
            'role'           => 'ROLE_USER',
        ];

        $form = $this->factory->create(AdminUserUpdateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
