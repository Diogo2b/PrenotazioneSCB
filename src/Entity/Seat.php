<?php

namespace App\Entity;

use App\Repository\SeatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeatRepository::class)]
class Seat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $seatNumber = null;

    #[ORM\ManyToOne(inversedBy: 'seats')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Row $row = null;

    /**
     * @var Collection<int, AboSeat>
     */
    #[ORM\OneToMany(targetEntity: AboSeat::class, mappedBy: 'seat')]
    private Collection $aboSeats;

    public function __construct()
    {
        $this->aboSeats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeatNumber(): ?int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(int $seatNumber): static
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    public function getRow(): ?Row
    {
        return $this->row;
    }

    public function setRow(?Row $row): static
    {
        $this->row = $row;

        return $this;
    }

    /**
     * @return Collection<int, AboSeat>
     */
    public function getAboSeats(): Collection
    {
        return $this->aboSeats;
    }

    public function addAboSeat(AboSeat $aboSeat): static
    {
        if (!$this->aboSeats->contains($aboSeat)) {
            $this->aboSeats->add($aboSeat);
            $aboSeat->setSeat($this);
        }

        return $this;
    }

    public function removeAboSeat(AboSeat $aboSeat): static
    {
        if ($this->aboSeats->removeElement($aboSeat)) {
            // set the owning side to null (unless already changed)
            if ($aboSeat->getSeat() === $this) {
                $aboSeat->setSeat(null);
            }
        }

        return $this;
    }
}
