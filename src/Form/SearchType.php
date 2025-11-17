<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\User;
use App\Entity\Users\UserGenders;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class SearchType extends AbstractType{
		
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder

		->add('category', TextType::class, [
			"label" => "Catégorie",
            'required' => true,
            'mapped' => false,
			"attr" => ['placeholder' => 'Catégorie']
		])
			
        ->add('location', TextType::class, [
            'mapped' => false,
            'required' => true,
            'label' => "Où ?",
			"attr" => ['placeholder' => 'Localisation']
        ]);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		//$resolver->setDefaults(['data_class' => User::class]);
	}
}
