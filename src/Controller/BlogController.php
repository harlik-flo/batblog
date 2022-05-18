<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewArticleFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{
    /*
     * Controleur de la page permettant de créer un nouvel article
     *
     * Accès réserver aux administrateur "ROLE_ADMIN"
     * */

    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[IsGranted('ROLE_ADMIN')]
    public function newPublication(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {

        $article = new Article();

        $form = $this->createForm(newArticleFormType::class, $article);

        $form->handleRequest($request);

//        Si le formulaire est envoyé et sans erreur
        if ($form->isSubmitted() && $form->isValid() ){

            //On termine l'hydratation de l'article
            $article
                ->setPublicationDate(new \DateTime())
                ->setAuthor( $this->getUser() )
                ->setSlug( $slugger->slug( $article->getTitle())->lower() )
            ;
            // Sauvegarde de l'article en BDD via le manager général des entités de Doctrine
            $em = $doctrine->getManager();

            $em->persist($article);

            $em->flush();
// Message flash de succès
            $this->addFlash('success','Article publié avec succès');

// Redirection vers la page qui affiche l'article (en envoyant sont id et sons slug dans l'url)
            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->render('blog/new_publication.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /*
     * Controleur de la page permettnet de voir un article en détail (via id et slug dans l'url)
     * */
    #[Route('/publication/{id}/{slug}/', name: 'publication_view')]
    #[ParamConverter('article', options: ['mapping' => ['id' => 'id', 'slug' => 'slug'] ])]
    public function publicationView(Article $article): Response
    {

        return $this->render('blog/publication_view.html.twig', [

            'article' => $article]

        );
    }



}
