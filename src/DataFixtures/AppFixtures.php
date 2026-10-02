<?php

namespace App\DataFixtures;

use App\Entity\Employe;
use App\Entity\Projet;
use App\Entity\Tache;
use App\Enum\StatutEmploye;
use App\Enum\StatutTache;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /*
         * Création des employés
         */
        $employes = [];

        for ($i = 0; $i < 10; $i++) {
            $dateEntree = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-8 years', '-1 month')
            );

            $employe = new Employe();
            $employe
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setEmail($faker->unique()->companyEmail())
                ->setDateEntree($dateEntree)
                ->setStatut(
                    $faker->randomElement(StatutEmploye::cases())
                );

            $employe->setPassword(
                $this->passwordHasher->hashPassword(
                    $employe,
                    'azerty'
                )
            );

            if ($i === 0) {
                $employe->setRoles(['ROLE_ADMIN']);
            }

            else
            {
                $employe->setRoles(['ROLE_USER']);
            }

            $manager->persist($employe);
            $employes[] = $employe;
        }

        /*
         * Création des projets
         */
        $projetsData = [
            [
                'nom' => 'Refonte du site internet',
                'archive' => false,
            ],
            [
                'nom' => 'Application mobile',
                'archive' => false,
            ],
            [
                'nom' => 'Migration du système informatique',
                'archive' => false,
            ],
            [
                'nom' => 'Portail des ressources humaines',
                'archive' => false,
            ],
            [
                'nom' => 'Ancien outil de gestion',
                'archive' => true,
            ],
        ];

        $nomsTaches = [
            'Analyser les besoins',
            'Créer les maquettes',
            'Préparer la base de données',
            'Développer les fonctionnalités',
            'Réaliser les tests',
            'Corriger les anomalies',
            'Rédiger la documentation',
            'Préparer la mise en production',
        ];

        foreach ($projetsData as $projetData) {
            $projet = new Projet();
            $projet
                ->setNom($projetData['nom'])
                ->setArchive($projetData['archive']);

            /*
             * Chaque projet reçoit entre 3 et 5 employés.
             */
            $clesEmployes = (array) array_rand(
                $employes,
                random_int(3, 5)
            );

            $membresDuProjet = [];

            foreach ($clesEmployes as $cleEmploye) {
                $employe = $employes[$cleEmploye];

                $projet->addEmploye($employe);
                $membresDuProjet[] = $employe;
            }

            $manager->persist($projet);

            /*
             * Création de six tâches pour chaque projet.
             */
            $titresSelectionnes = $faker->randomElements(
                $nomsTaches,
                6
            );

            foreach ($titresSelectionnes as $titre) {
                $tache = new Tache();
                $tache
                    ->setNom($titre)
                    ->setDescription($faker->sentence(12))
                    ->setDeadline(
                        \DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween('now', '+6 months')
                        )
                    )
                    ->setStatut(
                        $faker->randomElement(StatutTache::cases())
                    )
                    ->setProjet($projet);

                /*
                 * Environ 80 % des tâches sont assignées.
                 * L'employé choisi appartient obligatoirement au projet.
                 */
                if ($faker->boolean(80)) {
                    $tache->setEmploye(
                        $faker->randomElement($membresDuProjet)
                    );
                }

                $manager->persist($tache);
            }
        }

        $manager->flush();
    }
}