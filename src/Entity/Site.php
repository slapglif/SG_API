<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false,hardDelete=true)
 * @Gedmo\Loggable()
 */
class Site
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Sites uuid number.", type="integer", maxLength=255)
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Sites description.", type="string", maxLength=255)
     */
    private $description;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     * @Serializer\Exclude()
     * @SWG\Property(description="Sites creation date..", type="Datetime")
     */
    private $creation_date;

    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Is a site is currently active", type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Serializer\Groups("ROLE_GUARD")
     * @SWG\Property(description="Sites name.", type="string", maxLength=255)
     */
    private $name;

    /**
     * @Serializer\MaxDepth(1)
     * @ORM\OneToMany(targetEntity="App\Entity\GuardShift", mappedBy="site")
     */
    private $shifts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Perimeter", mappedBy="site")
     */
    private $perimeters;

    /**
     * @ORM\OneToMany(targetEntity=CheckPoint::class, mappedBy="site")
     */
    private $checkPoints;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tapFrequency;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="sites")
     */
    private $company;

    /**
     * Sites constructor.
     */
    public function __construct()
    {
        $this->creation_date = new DateTime();
        $this->shifts = new ArrayCollection();
        $this->perimeters = new ArrayCollection();
        $this->checkPoints = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @Serializer\Exclude()
     */
    public function getCreationDate(): ?DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(DateTimeInterface $creation_date): self
    {
        $this->creation_date = $creation_date;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("created_at")
     * @Serializer\Groups({"ROLE_ADMIN","ROLE_GUARD"})
     * @Serializer\Expose()
     * @return int
     */
    public function getRegTimestamp(): ?int
    {
        $regDate = $this->creation_date;

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
            $shift->setSite($this);
        }

        return $this;
    }

    public function removeShift(GuardShift $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getSite() === $this) {
                $shift->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @Serializer\MaxDepth(1)
     * @return Collection|Perimeter[]
     */
    public function getPerimeters(): Collection
    {
        return $this->perimeters;
    }

    public function addPerimeter(Perimeter $perimeter): self
    {
        if (!$this->perimeters->contains($perimeter)) {
            $this->perimeters[] = $perimeter;
            $perimeter->setSite($this);
        }

        return $this;
    }

    public function removePerimeter(Perimeter $perimeter): self
    {
        if ($this->perimeters->contains($perimeter)) {
            $this->perimeters->removeElement($perimeter);
            // set the owning side to null (unless already changed)
            if ($perimeter->getSite() === $this) {
                $perimeter->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CheckPoint[]
     */
    public function getCheckPoints(): Collection
    {
        return $this->checkPoints;
    }

    public function addCheckPoint(CheckPoint $checkPoint): self
    {
        if (!$this->checkPoints->contains($checkPoint)) {
            $this->checkPoints[] = $checkPoint;
            $checkPoint->setSite($this);
        }

        return $this;
    }

    public function removeCheckPoint(CheckPoint $checkPoint): self
    {
        if ($this->checkPoints->contains($checkPoint)) {
            $this->checkPoints->removeElement($checkPoint);
            // set the owning side to null (unless already changed)
            if ($checkPoint->getSite() === $this) {
                $checkPoint->setSite(null);
            }
        }

        return $this;
    }

    public function getTapFrequency(): ?int
    {
        return $this->tapFrequency;
    }

    public function setTapFrequency(?int $tapFrequency): self
    {
        $this->tapFrequency = $tapFrequency;

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
