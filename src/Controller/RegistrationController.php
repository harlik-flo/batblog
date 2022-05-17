<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /*
     * formulaire de crééation de compte */

    #[Route('/creer-un-compte', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

//        Si l'utilisateur est déja connecté, redirection sur la page d'acceuil
        if ($this->getUser()){
         return $this->redirectToRoute('main_home');
        }


        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hydratation du mot de passe avec le hashage du mot de passe venant du formulaire
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
//            Hydratation de la date d'inscription
            $user->setRegistationDate(new \DateTime() );



            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

//            Message flash de succès
            $this->addFlash('success', 'Votre compte a été créé avec succès !');


            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
