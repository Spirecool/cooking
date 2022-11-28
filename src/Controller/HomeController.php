<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_index')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        return $this->render('pages/home/home.html.twig', [
            'recipes' => $recipeRepository->findPublicRecipe(3)
        ]);
    }
}
