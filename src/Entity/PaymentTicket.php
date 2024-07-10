<?php

namespace App\Entity;

use App\Repository\PaymentTicketRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentTicketRepository::class)]
class PaymentTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paymentTickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payment $Payment = null;

    #[ORM\ManyToOne(inversedBy: 'paymentTickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ticket $ticket = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayment(): ?Payment
    {
        return $this->Payment;
    }

    public function setPayment(?Payment $Payment): static
    {
        $this->Payment = $Payment;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }
}
