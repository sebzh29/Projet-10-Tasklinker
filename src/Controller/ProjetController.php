<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Enum\StatutTache;
use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Projet;
use App\Form\ProjetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProjetController extends AbstractController
{
    #[Route(
        '/projet/{id}',
        name: 'app_projet_show',
        requirements: ['id' => '\d+']
    )]
    public function show(
        int $id,
        ProjetRepository $projetRepository
    ): Response {
        $projet = $projetRepository->findOneBy([
            'id' => $id,
            'archive' => false,
        ]);

        if ($projet === null) {
            throw $this->createNotFoundException(
                'Ce projet n’existe pas ou a été archivé.'
            );
        }

        $tachesParStatut = [
            StatutTache::A_FAIRE->value => [],
            StatutTache::EN_COURS->value => [],
            StatutTache::TERMINEE->value => [],
        ];

        foreach ($projet->getTaches() as $tache) {
            /** @var Tache $tache */
            $tachesParStatut[$tache->getStatut()->value][] = $tache;
        }

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'tachesParStatut' => $tachesParStatut,
        ]);
    }

    #[Route('/projet/nouveau', name: 'app_projet_new')]
public function new(
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $projet = new Projet();

    $form = $this->createForm(ProjetType::class, $projet);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($projet);
        foreach ($projet->getTaches() as $tache) {
            $employe = $tache->getEmploye();

            if (
                $employe !== null
                && !$projet->getEmployes()->contains($employe)
            ) {
                $tache->setEmploye(null);
            }
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_projet_show', [
            'id' => $projet->getId(),
        ]);
    }

    return $this->render('projet/new.html.twig', [
        'form' => $form,
    ]);
}

#[Route(
    '/projet/{id}/modifier',
    name: 'app_projet_edit',
    requirements: ['id' => '\d+']
)]
public function edit(
    int $id,
    Request $request,
    ProjetRepository $projetRepository,
    EntityManagerInterface $entityManager
): Response {
    $projet = $projetRepository->findOneBy([
        'id' => $id,
        'archive' => false,
    ]);

    if ($projet === null) {
        throw $this->createNotFoundException(
            'Ce projet n’existe pas ou a été archivé.'
        );
    }

    $form = $this->createForm(ProjetType::class, $projet);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_projet_show', [
            'id' => $projet->getId(),
        ]);
    }

    return $this->render('projet/edit.html.twig', [
        'projet' => $projet,
        'form' => $form,
    ]);
}

#[Route(
    '/projet/{id}/archiver',
    name: 'app_projet_archive',
    requirements: ['id' => '\d+'],
    methods: ['POST']
)]
public function archive(
    int $id,
    Request $request,
    ProjetRepository $projetRepository,
    EntityManagerInterface $entityManager
): Response {
    $projet = $projetRepository->findOneBy([
        'id' => $id,
        'archive' => false,
    ]);

    if ($projet === null) {
        throw $this->createNotFoundException(
            'Ce projet n’existe pas ou a été archivé.'
        );
    }

    $token = $request->getPayload()->getString('_token');

    if (!$this->isCsrfTokenValid('archive'.$projet->getId(), $token)) {
        throw $this->createAccessDeniedException(
            'Jeton CSRF invalide.'
        );
    }

    $projet->setArchive(true);
    $entityManager->flush();

    return $this->redirectToRoute('app_accueil');
}
}