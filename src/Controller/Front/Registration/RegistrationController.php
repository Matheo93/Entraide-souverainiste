<?php

namespace App\Controller\Front\Registration;

use App\Entity\User;
use App\Entity\Mails\Mails;
use App\Security\EmailVerifier;


use App\Form\RegistrationFormType;
use App\Repository\UserRepository;

use Symfony\Component\Mime\Address;

use App\Repository\Mails\MailsRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;



class RegistrationController extends AbstractController
{
	private $emailVerifier;
	private $userRepository;
	private $mailsRepository;

	public function __construct(EmailVerifier $emailVerifier, UserRepository $userRepository, MailsRepository $mailsRepository)
	{
		$this->emailVerifier = $emailVerifier;
		$this->userRepository = $userRepository;
		$this->mailsRepository = $mailsRepository;
	}

	/**
	 * @Route("/sinscrire", name="register_new_user", methods={"GET", "POST"})
	 */
	public function registerNewUser(Request $request,  UserPasswordEncoderInterface $passwordEncoder){
		$user = new User();

		$form = $this->createForm(RegistrationFormType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$rows = $request->request->get('registration_form');
			$user->setPassword(
				$passwordEncoder->encodePassword(
					$user,
					$form->get('plainPassword')->getData()
				)
			);
			$user->setRoles(["ROLE_USER"]);

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($user);
			$entityManager->flush();


			
			$subject = "Confirmation inscription";
			$receiver = $user->getEmail();
			$htmlTemplate = 'mails/front/registration/confirmation_email.html.twig';
			$TE = new TemplatedEmail();
			$TE->from(new Address($_ENV['APP_EMAIL_ADMIN'], $_ENV['APP_EMAIL_NAME']))->to($receiver)->subject($subject)->htmlTemplate($htmlTemplate);

			$this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $TE);

			$newMail = new Mails();

			$sender = $_ENV['APP_EMAIL_ADMIN'];
			//$content = $TE->getBody();
			$content = '';
	
			$newMail->setSubject($subject);
			$newMail->setContent($content);
			$newMail->setSender($sender);
			$newMail->setReceiver($receiver);
	
			$em = $this->getDoctrine()->getManager();
			$em->persist($newMail);
			$em->flush();
			
			$this->addFlash('success', 'Votre inscription a bien été enregistrée. Un email de confirmation vous a été adressé afin de valider votre adresse mail.');

			return $this->redirectToRoute('app_login');
		}
		elseif($form->isSubmitted() && !$form->isValid()){
			$this->addFlash('error', 'Le formulaire est incorrect');
		}

		return $this->render('front/registration/register_form.html.twig', [
			'registerForm' =>$form->createView(),
		]);
	}



	/**
	 * @Route("/verifier-son-email", name="app_verify_email")
	 */
	public function verifyUserEmail(Request $request): Response {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		// validate email confirmation link, sets User::isVerified=true and persists
		try {
			$this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
		} catch (VerifyEmailExceptionInterface $exception) {
			$this->addFlash('verify_email_error', $exception->getReason());

			return $this->redirectToRoute('app_register');
		}

		$this->addFlash('success', 'Votre adresse email a bien été vérifiée.');

		return $this->redirectToRoute('app_login');
	}

	/*public function formLogin(){
		return $this->render('front/registration/login.html.twig');
	}*/
}
