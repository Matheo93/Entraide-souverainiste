<?php

namespace App\Form\User;

use App\Entity\Announces\Announces;
use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class UserAnnouncesType extends AbstractType
{
    private $security;
    public function __construct(Security $security){
		$this->security = $security;
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $announce = $options['data'];
        $builder
        ->add('isRemote', CheckboxType::class, [
            'label' => 'Rendre ou bénéficier du service en distanciel',
            'required' => false,
            'attr' => []
        ])
        ->add('category', EntityType::class, [
            "class" => Categories::class,
            'choice_label' => 'name',
            'query_builder' => function (CategoriesRepository $c) {
                $categories = $c->createQueryBuilder('c')
                ->orderBy('c.name', 'ASC');
                return $categories;
            },
            "label" => "À quoi peut correspondre votre annonce ?",
            'required' => true,
            'mapped' => false,
            'data' => $announce->getCategories()[0]
        ])
        ->add('title', TextType::class, [
            "label" => "Quel est le titre de votre annonce ?",
            'attr' => ["placeholder" => "Offre d'emploi restauration - Paris", 'class' => ''],
            'required' => true,
        ])
        ->add('content', CKEditorType::class, [
            "label" => "Décrivez votre annonce en détails :",
            "required" => true,
            'config_name' => 'user_config',
        ])
        ->add('offer', ChoiceType::class, [
            'mapped' => false,
            "expanded" => true,
            'multiple' => false,
            "label" => "Est-ce une offre d'emploi ou une demande ?",
            'choices' => ['Demande' => 'proposal', 'Offre' => 'offer'],
            'data' => $announce->getAnnouncesMetas()->getMetas()['isOfferOrProposal']
        ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Announces::class,
        ]);
    }
}
