<?php

namespace App\Form;

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

class ExampleType extends AbstractType{
		
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder

		->add('gender', EntityType::class, [
			"class" => UserGenders::class,
			"label" => "Civilité",
			"attr" => ["class" => "mdl-radio mdl-js-radio mdl-js-ripple-effect"],
			"multiple" => false,
			'mapped' => false,
			'choice_label' => 'name'
		])
			->add('firstname', TextType::class, [
				'mapped' => false,
				"label" => "Prénom",
				"attr" => ['placeholder' => 'Prénom']
			])
			->add('lastname', TextType::class, [
				'mapped' => false,
				"label" => "Nom",
				"attr" => ['placeholder' => 'Nom']
			])
			->add('street', TextType::class, [
				'mapped' => false,
				"label" => "Adresse",
				"attr" => ['placeholder' => 'Adresse']
			])
			->add('postalCode', TextType::class, [
				'mapped' => false,
				"attr" => [
					'id' => 'postal_input',
					'placeholder' => 'Code Postal' 
				],
				'label' => 'Code Postal'
			])
			->add('city', TextType::class, [
				'mapped' => false,
				"label" => "Ville",
				"attr" => [
					'placeholder' => 'Ville', 
					'autocomplete' => "off",
				]
			])
			->add('phone', TextType::class, [
				'mapped' => false,
				"label" => "Téléphone",
				"attr" => [ 'placeholder' => 'Téléphone' ]
			])
			->add('email', RepeatedType::class, [
				'type' => EmailType::class,
				'first_options'  => ['label' => 'Email'],
				'second_options' => ['label' => 'Confirmer votre email'],
				'required' => true,
				'constraints' => [
					new NotBlank([
						'message' => 'Veuillez entrer une adresse email',
					]),
				],
			])
			->add('plainPassword', RepeatedType::class, [
				'type' => PasswordType::class,
				'first_options' => ['label' => 'Mot de passe'],
				'second_options' => ['label' => 'Confirmation du mot de passe'],
				'mapped' => false,
				'required' => true,
				'label'=> ' ',
				'attr' => [
					"placeholder" => 'Mot de passe',
				],
				'constraints' => [
					new NotBlank([
						'message' => 'Veuillez entrer un mot de passe',
					]),
					new Length([
						'min' => 6,
						'minMessage' => 'Votre mot de passe doit dépasser les {{ limit }} caractères',
						'max' => 4096,
					]),
				],
			])
			->add('agreeTerms', CheckboxType::class, [
				'mapped' => false,
				'required' => true,
				'label' => 'J’accepte que mes données personnelles soient utilisées dans le cadre de ma relation avec les équipes de Cercle Aristote. En aucun cas mes données ne seront communiquées à un tiers.',
				'constraints' => [
					new IsTrue([
						'message' => 'Veuillez accepter les conditions.',
					]),
				],
			])
			->add('agreeCGU', CheckboxType::class, [
				'mapped' => false,
				'required' => true,

				'label' => "J’accepte les Conditions Générales d'Utilisation.",
				'constraints' => [
					new IsTrue([
						'message' => 'Veuillez accepter les conditions d\'utilisation.',
					]),
				],
			])
			
			->add('');
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(['data_class' => User::class]);
	}
}
