<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\SportMatch;
use App\Entity\Seat;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price')
            ->add('status')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'placeholder' => 'Sélectionnez un utilisateur',
            ])
            ->add('sportMatch', EntityType::class, [
                'class' => SportMatch::class,
                'choice_label' => function (SportMatch $sportMatch) {
                    return $sportMatch->getHomeTeam() . ' vs ' . $sportMatch->getAwayTeam();
                },
                'label' => 'Match',
                'placeholder' => 'Sélectionnez un match',
            ])
            ->add('seat', EntityType::class, [
                'class' => Seat::class,
                'choice_label' => 'seatNumber',
                'placeholder' => 'Sélectionnez un siège',
            ])
            ->add('payment', EntityType::class, [
                'class' => Payment::class,
                'choice_label' => 'id',
                'placeholder' => 'Sélectionnez un paiement',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
