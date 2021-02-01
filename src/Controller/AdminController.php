<?php

declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AdminController extends AbstractController
{

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

}