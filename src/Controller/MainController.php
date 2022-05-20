<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


}
