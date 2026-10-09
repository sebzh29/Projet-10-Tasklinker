<?php

namespace App\Controller;

use App\Entity\Employe;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mon-compte/2fa')]
#[IsGranted('ROLE_USER')]
class TwoFactorSetupController extends AbstractController
{
    #[Route('/activer', name: 'app_2fa_setup', methods: ['GET', 'POST'])]
    public function setup(
        Request $request,
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof Employe) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isGoogleAuthenticatorEnabled()) {
            $this->addFlash('info', 'La double authentification est déjà activée.');

            return $this->redirectToRoute('app_accueil');
        }

        $session = $request->getSession();
        $sessionKey = '2fa_setup_secret_'.$user->getId();

        $secret = $session->get($sessionKey);

        if (!is_string($secret) || $secret === '') {
            $secret = $googleAuthenticator->generateSecret();
            $session->set($sessionKey, $secret);
        }

        // La clé est temporairement affectée à l'utilisateur,
        // sans être enregistrée en base de données.
        $user->setGoogleAuthenticatorSecret($secret);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid(
                '2fa_setup',
                $request->request->get('_token')
            )) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $code = trim((string) $request->request->get('code', ''));

            if ($googleAuthenticator->checkCode($user, $code)) {
                $entityManager->flush();
                $session->remove($sessionKey);

                $this->addFlash(
                    'success',
                    'La double authentification est activée.'
                );

                return $this->redirectToRoute('app_accueil');
            }

            $this->addFlash('danger', 'Code incorrect. Veuillez réessayer.');
        }

        return $this->render('security/2fa_setup.html.twig', [
            'secret' => $secret,
            'qrContent' => $googleAuthenticator->getQRContent($user),
        ]);
    }
}