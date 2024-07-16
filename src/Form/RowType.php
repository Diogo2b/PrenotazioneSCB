<?php

namespace App\Form;

use App\Entity\Row;
use App\Entity\Sector;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sigle')
            ->add('capacity')
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => function (Sector $sector) {
                    return $sector->getId() . ' - ' . $sector->getName();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Row::class,
        ]);
    }
}
