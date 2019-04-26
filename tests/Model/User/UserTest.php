<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\Email;
use App\Model\EmailGatewayMessageId;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\Token;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;
use App\Tests\FakeAr;
use Ramsey\Uuid\Uuid;

class UserTest extends BaseTestCase
{
    use UserTestTrait;

    public function testCreateByAdmin(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminSendInvite(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => true,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertFalse($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminNotActive(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            false,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => false,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertFalse($user->active());
    }

    public function testCreateByAdminDuplicateEmail(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\DuplicateEmail::class);

        User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerDuplicate
        );
    }

    public function testCreateByAdminMinimal(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user = User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\MinimalUserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminMinimalDuplicate(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $this->expectException(Exception\DuplicateEmail::class);

        User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerDuplicate
        );
    }

    public function testUpdateByAdmin(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user->updateByAdmin(
            $email,
            $role,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerNone
        );

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminUpdatedUser::class,
            [
                'email'     => $email->toString(),
                'role'      => $role->getValue(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testUpdateByAdminDuplicate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\DuplicateEmail::class);

        $user->updateByAdmin(
            $email,
            $role,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerDuplicate
        );
    }

    public function testChangePasswordByAdmin(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $password = $faker->password;

        $user->changePasswordByAdmin($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminChangedPassword::class,
            [
                'encodedPassword' => $password,
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testVerifyByAdmin(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true, // will set the user to unverified
            $this->userUniquenessCheckerNone
        );

        $user->verifyByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserVerifiedByAdmin::class,
            [],
            $events
        );

        $this->assertCount(2, $events);

        $this->assertTrue($user->verified());
    }

    public function testVerifyByAdminAlreadyVerified(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->verifyByAdmin();
    }

    public function testActivateByAdmin(): void
    {
        $user = $this->getUserInactive();

        $user->activateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserActivatedByAdmin::class,
            [],
            $events
        );

        $this->assertCount(2, $events);

        $this->assertTrue($user->active());
    }

    public function testActivateByAdminAlreadyActive(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->activateByAdmin();
    }

    public function testDeactivateByAdmin(): void
    {
        $user = $this->getUserActive();

        $user->deactivateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserDeactivatedByAdmin::class,
            [],
            $events
        );

        $this->assertCount(2, $events);

        $this->assertFalse($user->active());
    }

    public function testDeactivateByAdminAlreadyInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->deactivateByAdmin();
    }

    public function testInviteSent(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user->inviteSent($token, $messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\InviteSent::class,
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testInviteSentAlreadyVerified(): void
    {
        $faker = $this->faker();
        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->inviteSent($token, $messageId);
    }

    public function testVerify(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $user->verify();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(Event\UserVerified::class, [], $events);

        $this->assertCount(2, $events);

        $this->assertTrue($user->verified());
    }

    public function testVerifyAlreadyVerified(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->verify();
    }

    public function testVerifyInactive(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $user->deactivateByAdmin();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->verify();
    }

    public function testPasswordRecoverySent(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user->passwordRecoverySent($token, $messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\PasswordRecoverySent::class,
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testPasswordRecoverySentInactive(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->passwordRecoverySent($token, $messageId);
    }

    public function testUpdate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerNone
        );

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserUpdatedProfile::class,
            [
                'email'     => $email->toString(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testUpdateInactive(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();

        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerNone
        );
    }

    public function testUpdateDuplicate(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\DuplicateEmail::class);

        $user->update(
            $email,
            $firstName,
            $lastName,
            $this->userUniquenessCheckerDuplicate
        );
    }

    public function testLoggedIn(): void
    {
        $user = $this->getUserActive();

        $user->loggedIn();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserLoggedIn::class,
            [],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testLoggedInUnverified(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->expectException(Exception\UserNotVerified::class);

        $user->loggedIn();
    }

    public function testLoggedInInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->loggedIn();
    }

    public function testChangePassword(): void
    {
        $faker = $this->faker();

        $password = $faker->password;

        $user = $this->getUserActive();

        $user->changePassword($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\ChangedPassword::class,
            ['encodedPassword' => $password],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testChangePasswordInactive(): void
    {
        $faker = $this->faker();

        $password = $faker->password;

        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->changePassword($password);
    }

    public function testSameIdentityAs(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user1 = User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );
        $user2 = User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertTrue($user1->sameIdentityAs($user2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user1 = User::createByAdminMinimum(
            $faker->userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );
        $user2 = User::createByAdminMinimum(
            $faker->userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertFalse($user1->sameIdentityAs($user2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $user = User::createByAdminMinimum(
            $faker->userId,
            $faker->emailVo,
            $faker->password,
            Role::ROLE_USER(),
            $this->userUniquenessCheckerNone
        );

        $this->assertFalse($user->sameIdentityAs(FakeAr::create()));
    }
}
