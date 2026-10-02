<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TacheController extends AbstractController
{
    #[Route(
        '/projet/{id}/tache/nouvelle',
        name: 'app_tache_new',
        requirements: ['id' => '\d+']
    )]
    public function new(
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

        $tache = new Tache();
        $tache->setProjet($projet);

        $form = $this->createForm(TacheType::class, $tache, [
            'projet' => $projet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_show', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('tache/new.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route(
        '/tache/{id}/modifier',
        name: 'app_tache_edit',
        requirements: ['id' => '\d+']
    )]
    public function edit(
        int $id,
        Request $request,
        TacheRepository $tacheRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $tache = $tacheRepository->find($id);

        if (
            $tache === null
            || $tache->getProjet()->isArchive()
        ) {
            throw $this->createNotFoundException(
                'Cette tâche n’existe pas ou son projet a été archivé.'
            );
        }

        $projet = $tache->getProjet();

        $form = $this->createForm(TacheType::class, $tache, [
            'projet' => $projet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_show', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('tache/edit.html.twig', [
            'tache' => $tache,
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route(
        '/tache/{id}/supprimer',
        name: 'app_tache_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(
        int $id,
        Request $request,
        TacheRepository $tacheRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $tache = $tacheRepository->find($id);

        if (
            $tache === null
            || $tache->getProjet()->isArchive()
        ) {
            throw $this->createNotFoundException(
                'Cette tâche n’existe pas ou son projet a été archivé.'
            );
        }

        $projetId = $tache->getProjet()->getId();
        $token = $request->getPayload()->getString('_token');

        if (!$this->isCsrfTokenValid('delete'.$tache->getId(), $token)) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        $entityManager->remove($tache);
        $entityManager->flush();

        return $this->redirectToRoute('app_projet_show', [
            'id' => $projetId,
        ]);
    }
}