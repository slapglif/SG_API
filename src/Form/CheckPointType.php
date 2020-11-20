<?php

namespace App\Form;

use App\Entity\CheckPoint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckPointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('assetId')
            ->add('locationInformation')
            ->add('latitude')
            ->add('longitude')
            ->add('active')
            ->add('site');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CheckPoint::class,
                'csrf_protection' => false,
            ]
        );
    }
}
