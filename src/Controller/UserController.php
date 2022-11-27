<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/utilisateur/edition/{id}', name: 'app_user_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        // Vérifie que l'utilisateur est bien connecté, sinon cela le redigrige vers la page de login
        if(!$this->getUser()) {
            return $this->redirectToRoute('app_security_login');
        }

        // Vérifie si l'utilisateur courant est le même que celui récupéré dans l'id de la ligne 12 sinon on renvoie sur l'index des recettes
        if($this->getUser() !== $user) {
            return $this->redirectToRoute('app_recipe');
        }

        // On créé le formulaire
        $form = $this->createForm(UserType::class, $user );

        //on gère le submit
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            // si le mdp renseigné est le même mdp que celui renseigné en bdd alors on autorise
            if($hasher->isPasswordValid($user, $form->getData()->getPlainPassword() )){
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();
    
                $this->addFlash(
                    'success',
                    'Les informations de votre compte ont bien été modifiées.'
                );
    
                return $this->redirectToRoute('app_recipe');

            // si le mdp renseigné n'est pas le bon, cela envoie un message flash
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/edition-mot-de-passe/{id}', 'app_user_edit_password', methods: ['GET', 'POST'])] 
    public function editPassword(
        User $user,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher
    ): Response {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                // $choosenUser->setUpdatedAt(new \DateTimeImmutable());
                // $user->setPlainPassword(
                //     $form->getData()['newPassword']
                // );
                $user->setPassword(
                    $hasher->hashPassword(
                        $user, 
                        $form->getData()['newPassword']
                    )
                );

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Le mot de passe a bien été modifié.'
                );

                return $this->redirectToRoute('app_recipe');

            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

}