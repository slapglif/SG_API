<?php

namespace App\Form;

use App\Entity\Perimeter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PerimeterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('refNumber')
            ->add('latitude', TextType::class)
            ->add('longitude', TextType::class)
            ->add('site');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Perimeter::class,
                'csrf_protection' => false,
            ]
        );
    }
}
