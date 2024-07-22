<?php

// src/Form/PaymentType.php

namespace App\Form;

use App\Entity\Payment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01', // Ensure that the input accepts decimal values
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Complet' => true,
                    'Échoué' => false,
                ],
                'data' => true, // Default value
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName() . ' (ID: ' . $user->getId() . ')';
                },
                'placeholder' => 'Sélectionnez un utilisateur',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
