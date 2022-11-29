<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IngredientController extends AbstractController
{
    // Display all the ingredients
    #[IsGranted('ROLE_USER')]
    #[Route('/ingredient', name: 'app_ingredient')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        // $ingredients = $repository->findAll();
        $ingredients = $paginator->paginate(
            //on affiche uniquement les ingrédients reliés à l'utilisateur dans son index ingrédients
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10 /*limit per page*/
        );
        return $this->render('pages/ingredient/ingredient.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/ingredient/nouvel-ingredient', name: 'app_ingredient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $ingredient = $form->getData();
            //on lie un ingrédient à l'utilisateur lors de la création
            $ingredient->setUser($this->getUser());

            $em->persist($ingredient);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été créé avec succès'
            );

            return $this->redirectToRoute('app_ingredient');
        }
        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // L'utilisateur ne peut modifier que ses ingrédients, pas ceux des autres utilisateurs
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route('/ingredient/edition/{id}', name: 'app_ingredient_edit', methods: ['GET', 'POST'])]
    public function edit(Ingredient $ingredient, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $em->persist($ingredient);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été modifié avec succès'
            );

            return $this->redirectToRoute('app_ingredient');
        }
        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/ingredient/suppression/{id}', name: 'app_ingredient_delete', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    public function delete(EntityManagerInterface $em, Ingredient $ingredient): Response
    {
        // if(!$ingredient) {
        //     $this->addFlash(
        //         'success',
        //         'Votre ingrédient n\'existe pas'
        //     );
        //     return $this->redirectToRoute('app_ingredient');
        // }
            $em->remove($ingredient);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été supprimé avec succès'
            );

            return $this->redirectToRoute('app_ingredient');
        }
}
