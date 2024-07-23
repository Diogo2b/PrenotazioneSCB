<?php

namespace App\Form;

use App\Entity\PaymentTicket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('payment', EntityType::class, [
                'class' => 'App\Entity\Payment',
                'choice_label' => function ($payment) {
                    return $payment->getId() . ' - ' . $payment->getAmount();
                },
                'label' => 'Payment',
                'placeholder' => 'Sélectionnez un paiement',
            ])
            ->add('ticket', EntityType::class, [
                'class' => 'App\Entity\Ticket',
                'choice_label' => function ($ticket) {
                    return $ticket->getId() . ' - ' . $ticket->getPrice();
                },
                'label' => 'Ticket',
                'placeholder' => 'Sélectionnez un billet',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentTicket::class,
        ]);
    }
}
