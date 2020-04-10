<?php

declare(strict_types = 1);

namespace App\Form\Type;

use App\Entity\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		$builder->add('title', TextType::class)
			->add('link', TextType::class)
			->add('description', TextType::class)
			->add('save', SubmitType::class, ['label' => 'Přidat recept'])
		;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Recipe::class,
		]);
	}

}