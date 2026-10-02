<?php

namespace App\Controller;

use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(ProjetRepository $projetRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $projets = $projetRepository->findBy(
                ['archive' => false],
                ['id' => 'DESC']
            );
        } else {
            $projets = $projetRepository->findAccessibleByEmploye(
                $this->getUser()
            );
        }

        return $this->render('accueil/index.html.twig', [
            'projets' => $projets,
        ]);
    }
}