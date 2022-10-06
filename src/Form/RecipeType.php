<?php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('visibility', HiddenType::class)
            ->add('name', TextType::class)
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('disclaimer', TextType::class, [
                'required' => false,
            ])
            ->add('author', HiddenType::class)
            // ->add('ingredients')
            // ->add('steps')
            // ->add('recommendations')
            // ->add('created', HiddenType::class, ['data' => new \DateTime('now')])
            // ->add('updated', HiddenType::class, ['data' => new \DateTime('now')])
            ->add('save', SubmitType::class)
        ;

        // https://symfony.com/doc/current/reference/forms/types/collection.html#basic-usage
        // $builder->add('emails', CollectionType::class, [
        //     // each entry in the array will be an "email" field
        //     'entry_type' => EmailType::class,
        //     // these options are passed to each "email" type
        //     'entry_options' => [
        //         'attr' => ['class' => 'email-box'],
        //     ],
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
