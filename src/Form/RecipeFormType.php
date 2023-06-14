<?php

namespace App\Form;

use App\Model\Recipe\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RecipeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Název',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Popis',
                'attr' => ['style' => 'height: 50vh'],
                'help' => 'Popis lze formátovat pomocí markup language',
            ])
            ->add('link', UrlType::class, [
                'label' => 'Odkaz na původní recept',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Obrázek',
                'mapped' => false,
                'required' => false,
                'help' => 'Obrázek musí být ve formátu JPG',
                'constraints' => [
                    new File([
                        'maxSize' => '8192k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid jpeg or png image',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
