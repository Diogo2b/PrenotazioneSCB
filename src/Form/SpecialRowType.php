<?php
// src/Form/SpecialRowType.php

namespace App\Form;

use App\Entity\Row;
use App\Entity\Sector;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecialRowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sigle', null, [
                'label' => 'Sigle',
                'translation_domain' => false,
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacité',
                'data' => 20, // Valor padrão
                'translation_domain' => false,
            ])
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'name',
                'label' => 'Secteur',
                'placeholder' => 'Sélectionnez Secteur',
                'translation_domain' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Row::class,
        ]);
    }
}
