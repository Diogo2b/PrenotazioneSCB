<?php

namespace App\Entity;

use App\Repository\SectorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectorRepository::class)]
class Sector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $sigle = null;

    #[ORM\Column(nullable: true)]
    private ?bool $numberedSeats = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column]
    private ?bool $availableForSale = null;

    #[ORM\ManyToOne(inversedBy: 'sectors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tribune $tribune = null;

    /**
     * @var Collection<int, Row>
     */
    #[ORM\OneToMany(targetEntity: Row::class, mappedBy: 'sector')]
    private Collection $listRow;

    public function __construct()
    {
        $this->listRow = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(?string $sigle): static
    {
        $this->sigle = $sigle;

        return $this;
    }

    public function isNumberedSeats(): ?bool
    {
        return $this->numberedSeats;
    }

    public function setNumberedSeats(bool $numberedSeats): static
    {
        $this->numberedSeats = $numberedSeats;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function isAvailableForSale(): ?bool
    {
        return $this->availableForSale;
    }

    public function setAvailableForSale(bool $availableForSale): static
    {
        $this->availableForSale = $availableForSale;

        return $this;
    }

    public function getTribune(): ?Tribune
    {
        return $this->tribune;
    }

    public function setTribune(?Tribune $tribune): static
    {
        $this->tribune = $tribune;

        return $this;
    }

    /**
     * @return Collection<int, Row>
     */
    public function getListRow(): Collection
    {
        return $this->listRow;
    }

    public function addListRow(Row $listRow): static
    {
        if (!$this->listRow->contains($listRow)) {
            $this->listRow->add($listRow);
            $listRow->setSector($this);
        }

        return $this;
    }

    public function removeListRow(Row $listRow): static
    {
        if ($this->listRow->removeElement($listRow)) {
            // set the owning side to null (unless already changed)
            if ($listRow->getSector() === $this) {
                $listRow->setSector(null);
            }
        }

        return $this;
    }
}
