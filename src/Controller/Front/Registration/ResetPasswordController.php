<?php

namespace App\Controller\Front\Registration;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Service\MainService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/mot-de-passe-oublie")
 */
class ResetPasswordController extends AbstractController
{
	use ResetPasswordControllerTrait;

	private $resetPasswordHelper;
	private $mainSerivce;

	public function __construct(ResetPasswordHelperInterface $resetPasswordHelper, MainService $mainSerivce)
	{
		$this->resetPasswordHelper = $resetPasswordHelper;
		$this->mainSerivce = $mainSerivce;
	}

	/**
	 * Display & process form to request a password reset.
	 *
	 * @Route("", name="app_forgot_password_request")
	 */
	public function request(Request $request, MailerInterface $mailer): Response
	{
		$form = $this->createForm(ResetPasswordRequestFormType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			return $this->processSendingPasswordResetEmail(
				$form->get('email')->getData(),
				$mailer
			);
		}

		return $this->render('front/registration/reset_password/request.html.twig', [
			'requestForm' => $form->createView(),
		]);
	}

	/**
	 * Confirmation page after a user has requested a password reset.
	 *
	 * @Route("/verification", name="app_check_email")
	 */
	public function checkEmail(Request $request, MailerInterface $mailer): Response
	{
		/*// We prevent users from directly accessing this page
		if (!$this->canCheckEmail()) {
			return $this->redirectToRoute('app_forgot_password_request');
		}

		return $this->render('reset_password/check_email.html.twig', [
			'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
		]);*/
		$form = $this->createForm(ResetPasswordRequestFormType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			return $this->processSendingPasswordResetEmail(
				$form->get('email')->getData(),
				$mailer
			);			
		}

		$this->addFlash('alert', sprintf(
			'Un email contenant le lien de réinitialisation a été envoyé.Vous pouvez cliquer sur le lien pour réinitialiser votre mot de passe. Ce lien expirera dans 1 heure(s).',
		));		

		return $this->render('front/registration/reset_password/request.html.twig', [
			'requestForm' => $form->createView(),
		]);

	}

	/**
	 * Validates and process the reset URL that the user clicked in their email.
	 *
	 * @Route("/reinitialisation/{token}", name="app_reset_password")
	 */
	public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
	{
		if ($token) {
			// We store the token in session and remove it from the URL, to avoid the URL being
			// loaded in a browser and potentially leaking the token to 3rd party JavaScript.
			$this->storeTokenInSession($token);

			return $this->redirectToRoute('app_reset_password');
		}

		$token = $this->getTokenFromSession();
		if (null === $token) {
			throw $this->createNotFoundException('Aucun token trouvé dans l\'URL de réinitilisation.');
		}

		try {
			$user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
		} catch (ResetPasswordExceptionInterface $e) {
			$this->addFlash('reset_password_error', sprintf(
				'Il y a eu un problème avec la validation de votre demande de réinitialisation'));

			return $this->redirectToRoute('app_forgot_password_request');
		}

		// The token is valid; allow the user to change their password.
		$form = $this->createForm(ChangePasswordFormType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// A password reset token should be used only once, remove it.
			$this->resetPasswordHelper->removeResetRequest($token);

			// Encode the plain password, and set it.
			$encodedPassword = $passwordEncoder->encodePassword(
				$user,
				$form->get('plainPassword')->getData()
			);

			$user->setPassword($encodedPassword);
			$this->getDoctrine()->getManager()->flush();

			// The session is cleaned up after the password has been changed.
			$this->cleanSessionAfterReset();

			$this->addFlash('success', sprintf(
				'La modification de votre mot de passe a bien été sauvegardée!',
			));	
			return $this->redirectToRoute('account');
		}

		return $this->render('front/registration/reset_password/reset.html.twig', [
			'resetForm' => $form->createView(),
		]);
	}

	private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
	{
		
		$user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
			'email' => $emailFormData,
		]);

		// Marks that you are allowed to see the app_check_email page.
		$this->setCanCheckEmailInSession();

		// Do not reveal whether a user account was found or not.
		if (!$user) {
			return $this->redirectToRoute('app_check_email');
		}

		try {
			$resetToken = $this->resetPasswordHelper->generateResetToken($user);
		} catch (ResetPasswordExceptionInterface $e) {
			// If you want to tell the user why a reset email was not sent, uncomment
			// the lines below and change the redirect to 'app_forgot_password_request'.
			// Caution: This may reveal if a user is registered or not.
			//
			$this->addFlash('reset_password_error', sprintf(
				'Il y a eu un problème avec votre demande de réinitilisation - %s',
				$e->getReason()
			));
			
			return $this->redirectToRoute('app_check_email');
		}
		

		$userEmail  = $user->getEmail();
		/*$this->forward("App\Controller\Front\Mails\MailsController::sendMail", [
			"receiver" => $userEmail,
			"subject" => "Réinitialisation de mot de passe",
			"content_path" => "front/registration/reset_password/email.html.twig",
			"vars" => [
				'resetToken' => $resetToken,
				'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime()]
		]);*/
		$receiver = $userEmail;
		$subject = "Réinitialisation de mot de passe";
		$content_path = "front/registration/reset_password/email.html.twig";
		$vars = ['resetToken' => $resetToken,'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime()];
		$this->mainService->sendMail($receiver, $subject, $content_path, $vars);



		return $this->redirectToRoute('app_check_email');
	}
}
