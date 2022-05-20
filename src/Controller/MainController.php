<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\EditPhotoFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', name:'main_')]
class MainController extends AbstractController
{

    /**
     * Contrôleur de la page d'accueil
     */
    #[Route('/', name: 'home')]
    public function home(ManagerRegistry $doctrine): Response
    {
        $articleRepo = $doctrine->getRepository(Article::class);

        $articles = $articleRepo->findBy(
            [], // WHERE du SELECT
            ['publicationDate' => 'DESC'],  // ORDER BY du SELECT
            $this->getParameter('app.article.last_article_number_home'), // LIMIT du SELCET (qu'on récupère dans le services.yaml)
        );

        return $this->render('main/home.html.twig', [
            'articles' => $articles,
        ]);
    }

    /*
     * Controleur de la page de profil
     * */

    #[Route('/mon-profil/', name: 'profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(): Response
    {
        return $this->render('main/profil.html.twig');
    }

    /*
     * Controleur de la page de modification de la photo de profil
     *
     * Accès réservé aux connectés (ROLE_USER)
     *
     * */
    #[Route('/editer-photo/', name: 'edit_photo')]
    public function editPhoto(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(EditPhotoFormType::class);

        $form->handleRequest($request);

        //Si le formulaire a été envoyer et n'a pas d'erreur
        if ($form->isSubmitted() && $form->isValid())
        {
             //Récupération des informations de la photo envoyée
            $photo = $form->get('photo')->getData();

            // Si l'utilisateur a déja une photo de profil, on la supprime
            if(
                $this->getUser()->getPhoto() != null &&
                file_exists($this->getParameter('app.user.photo.directory'). $this->getUser()->getPhoto())
            ){
                unlink($this->getParameter('app.user.photo.directory'). $this->getUser()->getPhoto());
            }

            //Création nom pour la photo

            do{
                $newFileName = md5( random_bytes(100)) . '.' . $photo->guessExtension();
                dump($newFileName);
            } while(file_exists($this->getParameter('app.user.photo.directory') . $newFileName));

            //Sauvegarde du nom de la photo dans l'utilisateur connecté
            $this->getUser()->setPhoto($newFileName);
            //Sauvegarde en BDD
            $em = $doctrine->getManager();
            $em->flush();

            //déplacement phisique de l'image dans le dossier paramètre dans service.yaml
            $photo->move(
                $this->getParameter('app.user.photo.directory'),
                $newFileName,
            );

            //Message flash de succès
            $this->addFlash('success', 'Photo de profil enregistrée avec success');

            return $this->redirectToRoute('main_profil');

        }

        return $this->render('main/edit_photo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
