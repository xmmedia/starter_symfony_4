<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\StringUtil;

/**
 * @ORM\Entity(repositoryClass="App\Projection\User\UserFinder")
 */
class User implements UserInterface, PasswordHasherAwareInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var \Ramsey\Uuid\Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private \Ramsey\Uuid\UuidInterface $userId;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $verified = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $active = false;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $lastLogin = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $loginCount = 0;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $firstName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $lastName;

    /**
     * @var UserToken[]|Collection|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\UserToken", mappedBy="user")
     */
    private Collection $tokens;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
    }

    public function userId(): UserId
    {
        return UserId::fromUuid($this->userId);
    }

    public function email(): Email
    {
        return Email::fromString($this->email);
    }

    public function getUserIdentifier(): string
    {
        return $this->email()->toString();
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function password(): string
    {
        return $this->password;
    }

    /**
     * Allows setting the password when it changes while the user is logged in,
     * for example when their password is upgraded.
     */
    public function upgradePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }

    public function getPassword(): string
    {
        return $this->password();
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // nothing atm, object never stored un-encoded password
    }

    public function verified(): bool
    {
        return $this->verified;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function roles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = Role::ROLE_USER()->getValue();

        return array_unique($roles);
    }

    public function getRoles(): array
    {
        return $this->roles();
    }

    public function firstRole(): Role
    {
        return Role::byValue($this->roles[0]);
    }

    public function lastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function loginCount(): int
    {
        return $this->loginCount;
    }

    public function firstName(): ?Name
    {
        if (!isset($this->firstName) || null === $this->firstName) {
            return null;
        }

        return Name::fromString($this->firstName);
    }

    public function lastName(): ?Name
    {
        if (!isset($this->lastName) || null === $this->lastName) {
            return null;
        }

        return Name::fromString($this->lastName);
    }

    public function name(): ?string
    {
        return StringUtil::trim(
            sprintf('%s %s', $this->firstName, $this->lastName),
        );
    }

    public function getPasswordHasherName(): ?string
    {
        $adminRoles = [
            Role::ROLE_ADMIN()->getValue(),
            Role::ROLE_SUPER_ADMIN()->getValue(),
        ];
        if (\count(array_intersect($adminRoles, $this->roles)) > 0) {
            return 'harsh';
        }

        return null;
    }

    /**
     * User will be logged out if their email (username) or password changes,
     * or if they've become inactive or unverified.
     *
     * @param User|UserInterface $user the user just loaded from the db
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if ($this->password !== $user->password()) {
            return false;
        }

        if (!$this->email()->sameValueAs($user->email())) {
            return false;
        }

        if (!$user->active) {
            return false;
        }

        if (!$user->verified) {
            return false;
        }

        // check if roles have changed
        // sort so the arrays should end up the same
        $thisUserRoles = $this->roles();
        sort($thisUserRoles);
        $otherUserRoles = $user->roles();
        sort($otherUserRoles);
        if ($thisUserRoles !== $otherUserRoles) {
            return false;
        }

        return true;
    }
}
