<?php

namespace App\Form;

use App\Entity\Announces\Announces;
use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class AnnouncesType extends AbstractType
{
    private $security;
    private $categoriesRepository;
    public function __construct(Security $security, CategoriesRepository $categoriesRepository){
		$this->security = $security;
		$this->categoriesRepository = $categoriesRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isUser = $this->security->getUser() ? true : false;
        
        if(!$isUser){
            $builder
            ->add('email', TextType::class, [
                'attr' => ['placeholder' => 'jean.dupont@gmail.com'],
                'mapped' => false
            ]);
        }
       
        $builder->add('localisation', TextType::class, [
            'label' => 'Où se situe votre annonce ?'
        ])
            ->add('isRemote', CheckboxType::class, [
                'label' => 'Rendre ou bénéficier du service en distanciel',
                'attr' => [],
                'required' => false
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
                'choices' => ['Demande' => 'proposal', 'Offre' => 'offer']

            ])
        ;


        /*$formModifier = function (FormInterface $form, Announces $announce) {
            $categories = $announce->getCategories();
            if(count($categories) < 1) return;
            $categorySelected = $categories[0];

            $categoriesSlugsToAddField = [
                $this->categoriesRepository->find(5)->getSlug()
            ];
            if(in_array($categorySelected->getSlug(), $categoriesSlugsToAddField)){
                $form->add('offer', CheckboxType::class, [
                    'label' => "Est-ce une offre ou une demande ?",
                    'choices' => ['offre' => 'Offre', 'demande' => 'Demande'],
                    'mapped' => false,
                    'required' => true,
                 ]);
            }
		};

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier){
			$announce = $event->getData();
			$formModifier($event->getForm(), $announce);
		});*/
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Announces::class,
        ]);
    }
}
