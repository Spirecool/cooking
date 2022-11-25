<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController extends AbstractController
{
    // Display all the ingredients
    #[Route('/ingredient', name: 'app_ingredient')]
    public function index(IngredientRepository $ingredientRepository,PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $ingredientRepository -> findAll();
        // dd($ingredient);
        $ingredients = $paginator->paginate(
            $ingredientRepository->findAll(),
            $request->query->getInt('page', 1), 
            10 /*limit per page*/
        );
        return $this->render('pages/ingredient/ingredient.html.twig', [
            'ingredients'=> $ingredients
        ]);
    }

    #[Route('/ingredient/nouveau', name: 'app_ingredient_new', methods : ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em) : Response 
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()) {
            // dd($form->getData());
            $ingredient = $form->getData();

            $em->persist($ingredient);
            $em->flush();

            $this->addFlash (
                'success',
                'Votre ingrédient a été créé avec succès'
            );

            return $this->redirectToRoute('app_ingredient');
        }
        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}