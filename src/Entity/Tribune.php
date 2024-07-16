<?php

namespace App\Entity;

use App\Repository\TribuneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TribuneRepository::class)]
class Tribune
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $sigle = null;

    #[ORM\Column]
    private ?bool $numbered_seats = null;

    /**
     * @var Collection<int, Sector>
     */
    #[ORM\OneToMany(targetEntity: Sector::class, mappedBy: 'tribune')]
    private Collection $sectors;

    public function __construct()
    {
        $this->sectors = new ArrayCollection();
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
        return $this->numbered_seats;
    }

    public function setNumberedSeats(bool $numbered_seats): static
    {
        $this->numbered_seats = $numbered_seats;

        return $this;
    }

    /**
     * @return Collection<int, Sector>
     */
    public function getSectors(): Collection
    {
        return $this->sectors;
    }

    public function addSector(Sector $sector): static
    {
        if (!$this->sectors->contains($sector)) {
            $this->sectors->add($sector);
            $sector->setTribune($this);
        }

        return $this;
    }

    public function removeSector(Sector $sector): static
    {
        if ($this->sectors->removeElement($sector)) {
            // set the owning side to null (unless already changed)
            if ($sector->getTribune() === $this) {
                $sector->setTribune(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
