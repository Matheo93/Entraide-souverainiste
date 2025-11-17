<?php
namespace App\Controller\Front\Mails;

use App\Entity\Mails\Mails;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailsController extends AbstractController
{
	private $requestStack;
	private $mailer;

	public function __construct(MailerInterface $mailer, RequestStack $requestStack) {
		$this->mailer = $mailer;
		$this->requestStack = $requestStack;
	}

	public function sendMail($receiver, $subject, $content_path, $vars, $attachments = []){
		$request = $this->requestStack->getCurrentRequest();
		$parse = parse_url($request->server->get('HTTP_REFERER'));
		$vars['homeUrl'] = $parse['scheme']. "://".$parse['host'];

		$content = $this->render($content_path, $vars)->getContent();
		$email = (new TemplatedEmail())
			->from(new Address($_ENV['APP_EMAIL_ADMIN'], $_ENV['APP_EMAIL_NAME']))
			->to($receiver)
			->subject($subject)
			->htmlTemplate('mails/front/base.html.twig')
			->context(['content' => $content]);
		foreach($attachments as $a){
			$email->attachFromPath($a['path'], $a['name'], $a['type']);
		}
		$m = $this->mailer->send($email);

		$entity = $this->registerMail($receiver, $subject, $content);

	}

	public function registerMail($receiver, $subject, $content){
		$newMail = new Mails();

		$sender = $_ENV['APP_EMAIL_ADMIN'];

		$newMail->setSubject($subject);
		$newMail->setContent($content);
		$newMail->setSender($sender);
		$newMail->setReceiver($receiver);

		$em = $this->getDoctrine()->getManager();
		$em->persist($newMail);
		$em->flush();

		return $newMail;

	}

}
