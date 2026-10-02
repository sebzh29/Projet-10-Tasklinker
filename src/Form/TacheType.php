<?php

namespace App\Form;

use App\Entity\Employe;
use App\Entity\Projet;
use App\Entity\Tache;
use App\Enum\StatutTache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TacheType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        /** @var Projet $projet */
        $projet = $options['projet'];

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Titre de la tâche',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('statut', EnumType::class, [
                'class' => StatutTache::class,
                'label' => 'Statut',
                'choice_label' => static function (
                    StatutTache $statut
                ): string {
                    return $statut->value;
                },
            ])
            ->add('employe', EntityType::class, [
                'class' => Employe::class,
                'choices' => $projet->getEmployes()->toArray(),
                'choice_label' => static function (
                    Employe $employe
                ): string {
                    return $employe->getPrenom().' '.$employe->getNom();
                },
                'label' => 'Membre',
                'placeholder' => '',
                'required' => false,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);

        $resolver->setRequired('projet');
        $resolver->setAllowedTypes('projet', Projet::class);
    }
}