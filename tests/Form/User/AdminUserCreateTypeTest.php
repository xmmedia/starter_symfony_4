<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\AdminUserCreateType;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Tests\TypeTestCase;
use App\Validator\Constraints\UniqueNewUserEmailValidator;
use Mockery;

class AdminUserCreateTypeTest extends TypeTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $checker = Mockery::mock(ChecksUniqueUsersEmail::class);
        $checker->shouldReceive('__invoke')
            ->andReturnNull();

        $this->validatorContainer->set(
            UniqueNewUserEmailValidator::class,
            new UniqueNewUserEmailValidator($checker)
        );
    }

    public function test()
    {
        $faker = $this->faker();

        $formData = [
            'email'       => $faker->email,
            'setPassword' => true,
            'password'    => $faker->password,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
            'active'      => true,
            'sendInvite'  => true,
        ];

        $form = $this->factory->create(AdminUserCreateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }

    public function testNoPassword()
    {
        $faker = $this->faker();

        $formData = [
            'email'       => $faker->email,
            'setPassword' => false,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
            'active'      => true,
            'sendInvite'  => true,
        ];

        $form = $this->factory->create(AdminUserCreateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
