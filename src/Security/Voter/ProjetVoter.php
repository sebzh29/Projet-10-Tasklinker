<?php

namespace App\Security\Voter;

use App\Entity\Employe;
use App\Entity\Projet;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ProjetVoter extends Voter
{
    public const VIEW = 'PROJET_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW
            && $subject instanceof Projet;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        $user = $token->getUser();

        // L'utilisateur doit être connecté.
        if (!$user instanceof Employe) {
            $vote?->addReason('Vous devez être connecté.');

            return false;
        }

        // Les chefs de projet accèdent à tous les projets.
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // Les collaborateurs accèdent uniquement à leurs projets.
        if ($subject->getEmployes()->contains($user)) {
            return true;
        }

        $vote?->addReason(
            'Vous n’êtes pas affecté à ce projet.'
        );

        return false;
    }
}