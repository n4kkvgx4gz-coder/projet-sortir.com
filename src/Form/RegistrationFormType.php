<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le pseudo ne peut pas être vide']),
                    new Length([
                        'min' => 3,
                        'max' => 30,
                        'minMessage' => 'Le pseudo doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le pseudo ne peut pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom ne peut pas être vide']),
                    new Length([
                        'min' => 1,
                        'max' => 30,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractère',
                        'maxMessage' => 'Le prénom ne peut pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom ne peut pas être vide']),
                    new Length([
                        'min' => 3,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 15,
                        'maxMessage' => 'Le numéro de téléphone ne peut pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'email ne peut pas être vide']),
                    new Length([
                        'min' => 1,
                        'max' => 180,
                        'minMessage' => 'L\'email doit contenir au moins {{ limit }} caractère',
                        'maxMessage' => 'L\'email ne peut pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Vous devez accepter les conditions d\'utilisation']),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: "L'email ne peut pas être vide",
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'le mot de passe doit au moins contenir {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        max: 255,
                    ),
                ],
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom_campus',
                'label' => 'Campus de rattachement',
                'required' => true
            ])
            ->add('url_photo', FileType::class, [
                'required' => false,
                'mapped' => false
            ],
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
