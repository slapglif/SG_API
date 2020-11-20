<?php

namespace App\Entity;

use App\Repository\CheckpointInteractionRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=CheckpointInteractionRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false,hardDelete=true)
 * @Gedmo\Loggable()
 */
class CheckpointInteraction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CheckPoint::class, inversedBy="checkpointInteractions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $checkpoint;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="checkpointInteractions")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\MaxDepth(1)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $submitted;

    /**
     * @ORM\Column(type="boolean")
     */
    private $live;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToOne(targetEntity=GuardShift::class, inversedBy="checkpointInteractions")
     * @Serializer\MaxDepth(1)
     * @ORM\JoinColumn(nullable=false)
     */
    private $shift;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheckpoint(): ?CheckPoint
    {
        return $this->checkpoint;
    }

    public function setCheckpoint(?CheckPoint $checkpoint): self
    {
        $this->checkpoint = $checkpoint;

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

    public function getSubmitted(): ?DateTimeInterface
    {
        return $this->submitted;
    }

    public function setSubmitted(DateTimeInterface $submitted): self
    {
        $this->submitted = $submitted;

        return $this;
    }

    public function getLive(): ?bool
    {
        return $this->live;
    }

    public function setLive(bool $live): self
    {
        $this->live = $live;

        return $this;
    }

    public function getShift(): ?GuardShift
    {
        return $this->shift;
    }

    public function setShift(?GuardShift $shift): self
    {
        $this->shift = $shift;

        return $this;
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

    public function submitInfo($checkpoint, $user)
    {
        $this->setSubmitted(new DateTime());
        $this->setCheckpoint($checkpoint);
        $this->setUser($user);

        return $this;
    }
}
