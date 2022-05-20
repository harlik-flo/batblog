<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewArticleFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
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

    /*
     * Controleur de la page qui liste les articles
     * */
    #[Route('/publications/liste/', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupération de $_GET['page'], 1 si elle n'existe pas
        $requestedPage = $request->query->getInt('page',1);

        //Vérification que le nombre est positif
        if ($requestedPage < 1) {
            throw new NotAcceptableHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        $articles = $paginator->paginate(
            $query, //Requête créée juste avant
            $requestedPage, //Page qu'on souhaite voir
            10, //Nombre d'article à afficher par page
        );


        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles,
        ]);
    }

    /*
     * Controleur de la page admin servant à supprimer un article via son id dans l'url
     *
     * Accès reserver aux administrateur (ROLE_ADMIN)
     * */

    #[Route('/publication/suppression/{id}', name: 'publication_delete', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationDelete(Article $article, ManagerRegistry $doctrine, Request $request): Response
    {

        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('blog_publication_delete_'. $article->getId(), $csrfToken)){

            $this->addFlash('error','Token de sécurité invalide, veuillez ré-essayer.');

        }else{
            // Suppression de l'article en BDD
        $em = $doctrine->getManager();

        $em->remove($article);

        $em->flush();
        // Message flash de succès
        $this->addFlash('success', 'La publication a été supprimée avec succès !');

        }

        // Redirection vers la page qui liste les articles
        return $this->redirectToRoute('blog_publication_list');
    }

    /*
     * Controleur de la page admin servant à modifier un article via son id dans l'url
     *
     * Accès reserver aux administrateur (ROLE_ADMIN)
     * */

    #[Route('/publication/modifier/{id}', name: 'publication_edit', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationmodify(Article $article, ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        // Instanciation d'un nouveau formulaire basé sur $article qui contient déja les données actuelle de l'article à modifier
        $form = $this->createForm(NewArticleFormType::class, $article);

        $form->handleRequest($request);

        //formulaire est envoyé et sans erreur
        if($form->isSubmitted() && $form->isValid()){

            //Sauvegarder des donnés modifié en BDD
            $article->setSlug($slugger->slug( $article->getTitle() )->lower());
            $em = $doctrine->getManager();
            $em->flush();

            //Message flash de succès
            $this->addFlash('success', 'Publication modifiée avec succès !');

            //redirection vers l'article modifié
            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);
        }



        // Redirection vers la page qui liste les articles
        return $this->render('blog/publication_edit.html.twig', [
            'form' => $form->createView(),
            ]);

    }
}
