<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Form\EmployeType;
use App\Repository\EmployeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/equipe')]
class EmployeController extends AbstractController
{
    #[Route('', name: 'app_employe_index', methods: ['GET'])]
    public function index(
        EmployeRepository $employeRepository
    ): Response {
        return $this->render('employe/index.html.twig', [
            'employes' => $employeRepository->findBy(
                [],
                ['nom' => 'ASC']
            ),
        ]);
    }

    #[Route(
        '/{id}/modifier',
        name: 'app_employe_edit',
        methods: ['GET', 'POST']
    )]
    public function edit(
        Employe $employe,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(
            EmployeType::class,
            $employe
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_employe_index'
            );
        }

        return $this->render('employe/edit.html.twig', [
            'employe' => $employe,
            'form' => $form,
        ]);
    }

    #[Route(
        '/{id}/supprimer',
        name: 'app_employe_delete',
        methods: ['POST']
    )]
    public function delete(
        Employe $employe,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete'.$employe->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        /*
         * L'employé est retiré de tous ses projets.
         */
        foreach ($employe->getProjets() as $projet) {
            $employe->removeProjet($projet);
        }

        /*
         * Ses tâches sont conservées mais deviennent non assignées.
         */
        foreach ($employe->getTaches() as $tache) {
            $employe->removeTach($tache);
        }

        $entityManager->remove($employe);
        $entityManager->flush();

        return $this->redirectToRoute('app_employe_index');
    }
}