<?php

namespace App\Form;

use App\Entity\GuardShift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuardShiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'shift_start',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'input' => 'datetime',
                ]
            )
            ->add(
                'shift_end',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'input' => 'datetime',
                ]
            )
            ->add('user')
            ->add('site');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => GuardShift::class,
                'csrf_protection' => false,
            ]
        );
    }
}
