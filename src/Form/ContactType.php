<?php

namespace App\Form;

use App\Entity\ContactForm;

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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType{
		
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder

		->add('email', TextType::class, [
			"label" => "Email",
            'required' => true,
            'mapped' => false,
			"attr" => ['placeholder' => 'Email']
		])
		->add('name', TextType::class, [
			"label" => "Nom",
            'required' => true,
            'mapped' => false,
			"attr" => ['placeholder' => 'Nom']
		])
		->add('firstname', TextType::class, [
			"label" => "Prénom",
            'required' => true,
            'mapped' => false,
			"attr" => ['placeholder' => 'Prénom']
		])
		->add('phone', TextType::class, [
			"label" => "Téléphone",
            'required' => true,
            'mapped' => false,
			"attr" => ['placeholder' => 'Téléphone']
		])
		->add('message', TextareaType::class, [
			"label" => "Message",
            'required' => true,
            'mapped' => false,
			"attr" => ['placeholder' => 'Message', "class" => "materialize-textarea"]
		]);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(['data_class' => ContactForm::class]);
	}
}
