<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?SportMatch $sportMatch = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PaymentTicket>
     */
    #[ORM\OneToMany(targetEntity: PaymentTicket::class, mappedBy: 'ticket')]
    private Collection $paymentTickets;

    public function __construct()
    {
        $this->paymentTickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSportMatch(): ?SportMatch
    {
        return $this->sportMatch;
    }

    public function setSportMatch(?SportMatch $sportMatch): static
    {
        $this->sportMatch = $sportMatch;

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

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PaymentTicket>
     */
    public function getPaymentTickets(): Collection
    {
        return $this->paymentTickets;
    }

    public function addPaymentTicket(PaymentTicket $paymentTicket): static
    {
        if (!$this->paymentTickets->contains($paymentTicket)) {
            $this->paymentTickets->add($paymentTicket);
            $paymentTicket->setTicket($this);
        }

        return $this;
    }

    public function removePaymentTicket(PaymentTicket $paymentTicket): static
    {
        if ($this->paymentTickets->removeElement($paymentTicket)) {
            // set the owning side to null (unless already changed)
            if ($paymentTicket->getTicket() === $this) {
                $paymentTicket->setTicket(null);
            }
        }

        return $this;
    }
}
