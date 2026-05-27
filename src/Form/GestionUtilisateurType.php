<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GestionUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles',EntityType::class, [
                'class' => Campus::class,
                'choices' => [
                    'Administrateur' => '[ROLE_ADMIN]',
                    'Utilisateur' => '[ROLE_USER]'
                ]
            ] )
            ->add('password')
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('administrateur')
            ->add('actif', ChoiceType::class, ['choices'  => [
                'Yes' => 1,
                'No' => 0,
            ],])
            ->add('pseudo')
            ->add('url_photo', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER',
                ],
                'mapped' => false,
                'data' => in_array('ROLE_ADMIN', $builder->getData()->getRoles())
                    ? 'ROLE_ADMIN'
                    : 'ROLE_USER',
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom_campus',
                'label' => 'Campus de rattachement',
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
