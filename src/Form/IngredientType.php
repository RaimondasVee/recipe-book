<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Ingredient'])
            ->add('qty', NumberType::class, ['label' => 'Qty.'])
            ->add('unit', ChoiceType::class, ['label' => '',
                'choices' => [
                    'unit(s)' => 15,
                    'teaspoon(s)' => 2,
                    'tablespoon(s)' => 1,
                    'mililiter(s)' => 5,
                    'liter(s)' => 6,
                    'gram(s)' => 7,
                    'kilogram(s)' => 8,
                    'ounce(s)' => 9,
                    'fluid ounce(s)' => 10,
                    'quart(s)' => 11,
                    'pint(s)' => 12,
                    'gallon(s)' => 13,
                    'pound(s)' => 14,
                    'pinch(es)' => 16,
                    'hanful(s)' => 17,
                    'cup(s)' => 3,
                    'can(s)' => 4,
                ]
            ])
            ->add('recipeId', HiddenType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Add',
                'attr' => ['class' => 'btn-warning align-bottom']
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }
}