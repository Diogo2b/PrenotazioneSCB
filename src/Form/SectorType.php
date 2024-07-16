<?php

namespace App\Form;

use App\Entity\Sector;
use App\Entity\Tribune;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('sigle')
            ->add('numberedSeats')
            ->add('capacity')
            ->add('availableForSale')
            ->add('tribune', EntityType::class, [
                'class' => Tribune::class,
                'choice_label' => function (Tribune $tribune) {
                    return $tribune->getId() . ' - ' . $tribune->getName();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sector::class,
        ]);
    }
}
