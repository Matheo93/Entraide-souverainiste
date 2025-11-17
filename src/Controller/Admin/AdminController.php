<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\Mails\MailsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use FM\ElfinderBundle\Controller\ElFinderController;
use App\Repository\Reservations\ReservationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController {

	private $userRepository;
	private $mailsRepository;
	private $reservationsRepository;
	
	public function __construct(UserRepository $userRepository, MailsRepository $mailsRepository, ){
		$this->userRepository = $userRepository;
		$this->mailsRepository = $mailsRepository;
	}
	/**
	 * @Route("/", name="admin_home")
	 */
	public function index()
	{
		$lastUsers = $this->userRepository->findBy([], ['registered_at' => 'DESC'], '5');
		$lastMails = $this->mailsRepository->findBy([], ['send_at' => 'DESC'], '5');
		foreach($lastMails as &$mail){
			$userReceiver = $this->userRepository->findOneByEmail($mail->getReceiver());
			$mail->userReceiver = $userReceiver;
		}
		return $this->render('admin/index.html.twig', [
			'lastUsers' => $lastUsers,
			'lastMails' => $lastMails,
		]);
	}
}

?>