<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\DataProvider\RoleProvider;
use App\Form\User\AdminUserUpdateType;
use App\Form\DataTransformer\SecurityRoleTransformer;
use App\Model\Email;
use App\Model\User\Name;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Tests\TypeTestCase;
use App\Validator\Constraints\UniqueExistingUserEmailValidator;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AdminUserUpdateTypeTest extends TypeTestCase
{
    use MockeryPHPUnitIntegration;

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

        $roleTransformer = Mockery::mock(SecurityRoleTransformer::class);
        $roleTransformer->shouldReceive('transform')
            ->with(null)
            ->andReturnNull();
        $roleTransformer->shouldReceive('reverseTransform')
            ->with('ROLE_USER')
            ->andReturn(new Role('ROLE_USER'));

        $extensions[] = new AdminUserUpdateType(new RoleProvider($roleHierarchy), $roleTransformer);

        return $extensions;
    }

    public function test()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'userId'         => $faker->uuid,
            'changePassword' => true,
            'password'       => $faker->password(12, 250),
            'email'          => $faker->email,
            'firstName'      => $faker->name,
            'lastName'       => $faker->name,
            'role'           => 'ROLE_USER',
        ];

        $form = $this->factory->create(AdminUserUpdateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
        $this->assertInstanceOf(Role::class, $form->getData()['role']);
        $this->assertInstanceOf(Name::class, $form->getData()['firstName']);
        $this->assertInstanceOf(Name::class, $form->getData()['lastName']);
    }

    public function testNoPassword()
    {
        $faker = Faker\Factory::create();

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

        $this->hasAllFormFields($form, $formData);
    }
}
