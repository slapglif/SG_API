<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false,hardDelete=true)
 * @Gedmo\Loggable()
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @SWG\Property(description="The unique identifier of the user.")
     * @Serializer\Groups({"ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Serializer\Groups({"ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @SWG\Property(description="The registed email address of the user.", type="string", maxLength=255)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Serializer\Accessor(getter="getRoles",setter="setRoles")
     * @Serializer\Groups({"ROLE_GUARD","ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @SWG\Property(description="Access roles applied to users account.", type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Serializer\Exclude()
     * @SWG\Property(description="Encrypted password.", type="string", maxLength=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"ROLE_GUARD","ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @SWG\Property(description="Account holders Forename.", type="string", maxLength=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"ROLE_GUARD","ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @SWG\Property(description="Account holders surname.", type="string", maxLength=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Serializer\Groups({"ROLE_GUARD","ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @Serializer\Exclude()
     */
    private $lastLoggedIn;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Groups({"ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @SWG\Property(description="Json of account holders qualifications", type="json")
     */
    private $qualifications = [];

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"ROLE_GUARD"})
     * @SWG\Property(description="Default pay rate for Guard.", type="integer")
     */
    private $default_pay_rate;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Serializer\Groups({"ROLE_GUARD","ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @Serializer\Exclude()
     */
    private $registration_date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GuardShift", mappedBy="user")
     * @Serializer\MaxDepth(1)
     */
    private $shifts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GuardShift", mappedBy="admin")
     * @Serializer\MaxDepth(1)
     */
    private $administratedShifts;

    /**
     * @Serializer\Groups({"ROLE_GUARD","ROLE_ADMIN","ROLE_FARRELL_TECH"})
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;
    /**
     * @ORM\OneToMany(targetEntity=CheckpointInteraction::class, mappedBy="user")
     * @Serializer\MaxDepth(1)
     */
    private $checkpointInteractions;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="users")
     */
    private $company;

    public function __construct()
    {
        $this->shifts = new ArrayCollection();
        $this->administratedShifts = new ArrayCollection();
        $this->checkpointInteractions = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param mixed $deletedAt
     */
    public function setDeletedAt($deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_GUARD
        $roles[] = 'ROLE_GUARD';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @Serializer\Exclude()
     */
    public function getLastLoggedIn()
    {
        return $this->lastLoggedIn;
    }

    public function setLastLoggedIn(DateTimeInterface $lastLoggedIn): self
    {
        $this->lastLoggedIn = $lastLoggedIn;

        return $this;
    }

    public function getQualifications(): ?array
    {
        return $this->qualifications;
    }

    public function setQualifications(?array $qualifications): self
    {
        $this->qualifications = $qualifications;

        return $this;
    }

    public function getDefaultPayRate(): ?int
    {
        return $this->default_pay_rate;
    }

    public function setDefaultPayRate(?int $default_pay_rate): self
    {
        $this->default_pay_rate = $default_pay_rate;

        return $this;
    }

    public function getRegistrationDate(): ?DateTimeInterface
    {
        return $this->registration_date;
    }

    public function setRegistrationDate(DateTimeInterface $registration_date): self
    {
        $this->registration_date = $registration_date;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("last_logged_in")
     * @Serializer\Groups({"ROLE_ADMIN","ROLE_GUARD","ROLE_FARRELL_TECH"})
     * @Serializer\Expose()
     * @SWG\Property(description="Timestamp of when the user last requested a new token.", type="DateTime")
     * @return int
     */
    public function getCreationTimestamp(): ?int
    {
        $lastLogIn = $this->lastLoggedIn;

        return $lastLogIn->getTimestamp();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("reg_date")
     * @Serializer\Groups({"ROLE_ADMIN","ROLE_GUARD","ROLE_FARRELL_TECH"})
     * @Serializer\Expose()
     * @SWG\Property(description="Timestamp of when the user registed the account.", type="DateTime")
     * @return int
     */
    public function getRegTimestamp(): ?int
    {
        $regDate = $this->registration_date;

        return $regDate->getTimestamp();
    }

    /**
     * @return Collection|GuardShift[]
     */
    public function getShifts(): Collection
    {
        return $this->shifts;
    }

    public function addShift(GuardShift $shift): self
    {
        if (!$this->shifts->contains($shift)) {
            $this->shifts[] = $shift;
            $shift->setUser($this);
        }

        return $this;
    }

    public function removeShift(GuardShift $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getUser() === $this) {
                $shift->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|GuardShift[]
     */
    public function getAdministratedShifts(): Collection
    {
        return $this->administratedShifts;
    }

    public function addAdministratedShift(GuardShift $administratedShift): self
    {
        if (!$this->administratedShifts->contains($administratedShift)) {
            $this->administratedShifts[] = $administratedShift;
            $administratedShift->setAdmin($this);
        }

        return $this;
    }

    public function removeAdministratedShift(GuardShift $administratedShift): self
    {
        if ($this->administratedShifts->contains($administratedShift)) {
            $this->administratedShifts->removeElement($administratedShift);
            // set the owning side to null (unless already changed)
            if ($administratedShift->getAdmin() === $this) {
                $administratedShift->setAdmin(null);
            }
        }

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|CheckpointInteraction[]
     */
    public function getCheckpointInteractions(): Collection
    {
        return $this->checkpointInteractions;
    }

    public function addCheckpointInteraction(CheckpointInteraction $checkpointInteraction): self
    {
        if (!$this->checkpointInteractions->contains($checkpointInteraction)) {
            $this->checkpointInteractions[] = $checkpointInteraction;
            $checkpointInteraction->setUser($this);
        }

        return $this;
    }

    public function removeCheckpointInteraction(CheckpointInteraction $checkpointInteraction): self
    {
        if ($this->checkpointInteractions->contains($checkpointInteraction)) {
            $this->checkpointInteractions->removeElement($checkpointInteraction);
            // set the owning side to null (unless already changed)
            if ($checkpointInteraction->getUser() === $this) {
                $checkpointInteraction->setUser(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
