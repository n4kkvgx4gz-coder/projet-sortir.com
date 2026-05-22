<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Sortie;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Titre',
            ])

            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une catégorie',
            ])
            ->add('dateDebut')
            ->add('dateCloture')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nomCampus',
                'placeholder' => 'Choisir une campus',
                'mapped' => false,
            ])
            ->add('nbInscriptionsMax')
            ->add('description')

            ->add('nomLieu', TextType::class, [
                'mapped' => false,
                'label' => 'Nom du lieu',
            ])
            ->add('rue', TextType::class, [
                'mapped' => false,
                'label' => 'Adresse',
            ])
            ->add('ville', TextType::class, [
                'mapped' => false,
                'label' => 'Ville',
                'required' => true,
            ])
            ->add('codePostal', TextType::class, [
                'mapped' => false,
                'label' => 'Code postal',
                'required' => true,
            ])

            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File(
                        maxSize: '1024k',
                        extensions: ['png', 'jpg', 'jpeg'],
                        extensionsMessage: 'Les images doivent être au format JPG, JPEG ou PNG et faire moins de 1024k',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
