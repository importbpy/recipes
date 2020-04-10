<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\Type\RecipeType;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController
{

	/**
	 * @var \App\Repository\RecipeRepository
	 */
	private $recipeRepository;

	public function __construct(
		RecipeRepository $recipeRepository
	) {
		$this->recipeRepository = $recipeRepository;
	}

	/**
	 * @Route(
	 *     "/pridat-recept/",
	 *     name="new_recipe"
	 * )
	 */
	public function form(Request $request)
	{
		$recipe = new Recipe();

		$form = $this->createForm(RecipeType::class, $recipe);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$recipe = $form->getData();

			 $entityManager = $this->getDoctrine()->getManager();
			 $entityManager->persist($recipe);
			 $entityManager->flush();

			return $this->redirectToRoute('homepage');
		}


		return $this->render('form.html.twig', [
			'form' => $form->createView(),
		]);
	}

}