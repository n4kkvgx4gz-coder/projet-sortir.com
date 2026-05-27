<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Sortie;
use App\Entity\Campus;
use App\Entity\Lieu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SortieType extends AbstractType
{

    // test 123
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Titre',
            ])
            ->add('dateDebut', null, [
                'label' => 'Début',
            ])
            ->add('dateCloture', null, [
                'label' => 'Fin',
            ])
            ->add('nbInscriptionsMax', null, [
                'label' => 'Nombre de places',
            ])
            ->add('description')

            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => function (Lieu $lieu) {
                    return $lieu->getNomLieu()
                        . ' — '
                        . $lieu->getVille()->getNomVille()
                        . ' — '
                        . substr($lieu->getVille()->getCodePostal(), 0, 2);
                },
                'placeholder' => 'Rechercher un lieu',
                'attr' => [
                    'class' => 'tom-select-lieu',
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une catégorie',
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nomCampus',
                'placeholder' => 'Choisir un campus',
                'mapped' => false,
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
