<?php

namespace App\Entity;

use App\Repository\CheckPointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=CheckPointRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false,hardDelete=true)
 * @Gedmo\Loggable()
 */
class CheckPoint
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="checkPoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $assetId;

    # Field set to to text as client needs a large amount of space that exceeds varchar.
    /**
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $locationInformation;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\OneToMany(targetEntity=CheckpointInteraction::class, mappedBy="checkpoint")
     */
    private $checkpointInteractions;

    public function __construct()
    {
        $this->checkpointInteractions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getAssetId(): ?string
    {
        return $this->assetId;
    }

    public function setAssetId(string $assetId): self
    {
        $this->assetId = $assetId;

        return $this;
    }

    public function getLocationInformation()
    {
        return $this->locationInformation;
    }

    public function setLocationInformation($locationInformation): self
    {
        $this->locationInformation = $locationInformation;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

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
            $checkpointInteraction->setCheckpoint($this);
        }

        return $this;
    }

    public function removeCheckpointInteraction(CheckpointInteraction $checkpointInteraction): self
    {
        if ($this->checkpointInteractions->contains($checkpointInteraction)) {
            $this->checkpointInteractions->removeElement($checkpointInteraction);
            // set the owning side to null (unless already changed)
            if ($checkpointInteraction->getCheckpoint() === $this) {
                $checkpointInteraction->setCheckpoint(null);
            }
        }

        return $this;
    }
}
