<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $idPayment = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PaymentTicket>
     */
    #[ORM\OneToMany(targetEntity: PaymentTicket::class, mappedBy: 'Payment')]
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getIdPayment(): ?string
    {
        return $this->idPayment;
    }

    public function setIdPayment(string $idPayment): static
    {
        $this->idPayment = $idPayment;

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
            $paymentTicket->setPayment($this);
        }

        return $this;
    }

    public function removePaymentTicket(PaymentTicket $paymentTicket): static
    {
        if ($this->paymentTickets->removeElement($paymentTicket)) {
            // set the owning side to null (unless already changed)
            if ($paymentTicket->getPayment() === $this) {
                $paymentTicket->setPayment(null);
            }
        }

        return $this;
    }
}
