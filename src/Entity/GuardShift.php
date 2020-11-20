<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GuardShiftRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false,hardDelete=true)
 * @Gedmo\Loggable()
 */
class GuardShift
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups("ROLE_GUARD")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Shifts provided start time.", type="Datetime")
     */
    private $shift_start;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Shifts provided finish time", type="Datetime")
     */
    private $shift_end;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Guards clocked in start time", type="Datetime")
     */
    private $actual_shift_start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Guards clocked in finish time", type="Datetime")
     */
    private $actual_shift_end;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shifts")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\MaxDepth(1)
     * @Serializer\Groups("ROLE_GUARD")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="administratedShifts")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\MaxDepth(1)
     * @Serializer\Groups("ROLE_GUARD")
     */
    private $admin;

    /**
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="shifts")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\MaxDepth(1)
     * @Serializer\Groups("ROLE_GUARD")
     */
    private $site;

    /**
     * @ORM\Column(name="approved", type="integer", nullable=false)
     */
    private $approved;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;
    /**
     * @Serializer\MaxDepth(1)
     * @ORM\OneToMany(targetEntity=CheckpointInteraction::class, mappedBy="shift")
     */
    private $checkpointInteractions;

    public function __construct()
    {
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

    public function getShiftStart(): ?DateTimeInterface
    {
        return $this->shift_start;
    }

    public function setShiftStart(DateTimeInterface $shift_start): self
    {
        $this->shift_start = $shift_start;

        return $this;
    }

    public function getShiftEnd(): ?DateTimeInterface
    {
        return $this->shift_end;
    }

    public function setShiftEnd(DateTimeInterface $shift_end): self
    {
        $this->shift_end = $shift_end;

        return $this;
    }

    public function getActualShiftStart(): ?DateTimeInterface
    {
        return $this->actual_shift_start;
    }

    public function setActualShiftStart(?DateTimeInterface $actual_shift_start): self
    {
        $this->actual_shift_start = $actual_shift_start;

        return $this;
    }

    public function getActualShiftEnd(): ?DateTimeInterface
    {
        return $this->actual_shift_end;
    }

    public function setActualShiftEnd(?DateTimeInterface $actual_shift_end): self
    {
        $this->actual_shift_end = $actual_shift_end;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getApproved(): ?int
    {
        return $this->approved;
    }

    public function setApproved(?int $approved): self
    {
        $this->approved = $approved;

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
            $checkpointInteraction->setShift($this);
        }

        return $this;
    }

    public function removeCheckpointInteraction(CheckpointInteraction $checkpointInteraction): self
    {
        if ($this->checkpointInteractions->contains($checkpointInteraction)) {
            $this->checkpointInteractions->removeElement($checkpointInteraction);
            // set the owning side to null (unless already changed)
            if ($checkpointInteraction->getShift() === $this) {
                $checkpointInteraction->setShift(null);
            }
        }

        return $this;
    }
}
