<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Enum\StatutEmploye;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $employe = new Employe();

        $form = $this->createForm(RegistrationFormType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $employe->setPassword(
                $passwordHasher->hashPassword(
                    $employe,
                    $employe->getPassword()
                )
            );

            $employe->setDateEntree(new \DateTimeImmutable());

            $employe->setStatut(StatutEmploye::CDI);

            $entityManager->persist($employe);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/bienvenue', name: 'app_welcome')]
    public function welcome(): Response
    {
        return $this->render('registration/welcome.html.twig');
    }
}