<?php

namespace App\Form\Admin\Announce;

use App\Entity\Categories;
use App\Entity\Announces\Announces;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
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

class AdminAnnounceFormType extends AbstractType{
		
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
		->add('title')
		->add('content', CKEditorType::class,[
			'config_name' => 'main_config'
		])
		->add('category', EntityType::class, [
			"class" => Categories::class,
			'choice_label' => 'name',
			"label" => "Catégorie",
			'required' => true,
			'mapped' => false,
		])
		->add('isActive')
		;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(['data_class' => Announces::class]);
	}
}
