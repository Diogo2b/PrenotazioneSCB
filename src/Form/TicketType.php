<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\SportMatch;
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
