<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\DataProvider\RoleProvider;
use App\Form\User\AdminUserUpdateType;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Tests\TypeTestCase;
use App\Validator\Constraints\UniqueExistingUserEmailValidator;
use Mockery;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AdminUserUpdateTypeTest extends TypeTestCase
{
    public function setUp(): void
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

    protected function getTypes()
    {
        $extensions = parent::getTypes();

        $roleHierarchy = Mockery::mock(RoleHierarchyInterface::class);
        $roleHierarchy->shouldReceive('getReachableRoles')
            ->andReturn([new Role('ROLE_USER')]);

        $extensions[] = new AdminUserUpdateType(
            new RoleProvider($roleHierarchy)
        );

        return $extensions;
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
