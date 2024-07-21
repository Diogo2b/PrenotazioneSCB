<?php

namespace App\Form;

use App\Entity\PriceType;
use App\Entity\SportMatch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// src/Form/SportMatchType.php

class SportMatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('homeTeam', TextType::class, [
                'attr' => ['style' => 'text-transform:uppercase;'],
            ])
            ->add('awayTeam', TextType::class, [
                'attr' => ['style' => 'text-transform:uppercase;'],
            ])
            ->add('matchDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('priceType', EntityType::class, [
                'class' => PriceType::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SportMatch::class,
        ]);
    }
}
