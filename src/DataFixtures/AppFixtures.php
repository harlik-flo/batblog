<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    /*
     * Stockage du service d'encodage des mots de passe de symfony
     * */
    private $encoder;
    private $slugger;

    /*
     * On utilise le constructeur pour demander à symfony de récupérer le service d'encodage des mot de passe pour ensuiute le stocker dans this->>encoder
     * */

    public function __construct(UserPasswordHasherInterface $encoder, SluggerInterface $slugger){
        $this->encoder = $encoder;
        $this->slugger = $slugger;

    }

    public function load(ObjectManager $manager): void
    {
        // Instantiation du Faker en langue fr
        $faker = Faker\Factory::create('fr_FR');


        // Création du compte admin de Batman
        $admin = new User();

        $admin
            ->setEmail('admin@a.a')
            ->setRegistationDate( $faker->dateTimeBetween('-1 year','now'))
            ->setPseudonym('Batman')
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword(
                $this->encoder->hashPassword($admin,'123456.Admin')
            )
        ;

        $manager->persist($admin);

        for($i = 0; $i < 10; $i++){

            $user = new User();

            $user
                ->setEmail( $faker->email)
                ->setRegistationDate($faker->dateTimeBetween('-10 year','now'))
                ->setPseudonym($faker->userName)
                ->setPassword(
                    $this->encoder->hashPassword($user,'123456.Admin')
                )
            ;
            $manager->persist($user);
        }

        //Création de 200 articles (avec une boucle)

        for($i = 0; $i < 200; $i++){
            $article = new Article();

            $article
                ->setTitle($faker->sentence(10))
                ->setContent( $faker->paragraph(15) )
                ->setPublicationDate($faker->dateTimeBetween('-10 year','now'))
                ->setAuthor($admin) //Batman sera l'auteur de tout les articles
                ->setSlug($this->slugger->slug($article->getTitle())->lower());

            $manager->persist($article);
        }

        $manager->flush();
    }

    private function setRoles(array $array)
    {
    }
}
