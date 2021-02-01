<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Model\Recipe\NewRecipe;
use App\Model\Recipe\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AdminController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(
     *     "/admin",
     *     name="admin_homepage"
     * )
     */
    public function adminHomepage(): Response
    {
        return $this->render(
            'admin.base.html.twig'
        );
    }

    /**
     * @Route(
     *     "/admin/new-recipe",
     *     name="create_new_recipe"
     * )
     */
    public function addNewRecipe(Request $request): Response
    {
        $newRecipe = new NewRecipe();

        $form = $this->createFormBuilder($newRecipe)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('image', FileType::class)
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $recipe = new Recipe(
                $newRecipe->getTitle(),
                $newRecipe->getDescription(),
                null,
            );
            $this->entityManager->persist($recipe);

            $this->entityManager->flush();
            $image = $newRecipe->getImage();
            if ($image !== null) {
                $image->move(__DIR__ . '/../../public/images/original', $recipe->getTitle() . '.jpg');
            }

            $image = imagecreatefromjpeg(__DIR__ . '/../../public/images/original/' . $recipe->getTitle() . '.jpg');
            $imgResized = imagescale($image , 400);
            imagejpeg($imgResized, __DIR__ . '/../../public/images/small/' . $recipe->getTitle() . '.jpg');
            $imgResized = imagescale($image , 1200);
            imagejpeg($imgResized, __DIR__ . '/../../public/images/' . $recipe->getTitle() . '.jpg');
        }

        return $this->render(
            '/admin/addNewRecipe.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

}