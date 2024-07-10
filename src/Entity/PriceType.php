<?php

namespace App\Entity;

use App\Repository\PriceTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceTypeRepository::class)]
class PriceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    /**
     * @var Collection<int, SportMatch>
     */
    #[ORM\OneToMany(targetEntity: SportMatch::class, mappedBy: 'priceType')]
    private Collection $sportMatches;

    public function __construct()
    {
        $this->sportMatches = new ArrayCollection();
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, SportMatch>
     */
    public function getSportMatches(): Collection
    {
        return $this->sportMatches;
    }

    public function addSportMatch(SportMatch $sportMatch): static
    {
        if (!$this->sportMatches->contains($sportMatch)) {
            $this->sportMatches->add($sportMatch);
            $sportMatch->setPriceType($this);
        }

        return $this;
    }

    public function removeSportMatch(SportMatch $sportMatch): static
    {
        if ($this->sportMatches->removeElement($sportMatch)) {
            // set the owning side to null (unless already changed)
            if ($sportMatch->getPriceType() === $this) {
                $sportMatch->setPriceType(null);
            }
        }

        return $this;
    }
}
