<?php

namespace App\Controller\Front\Users;

use App\Entity\User;
use App\Entity\Announces\Announces;

use App\Form\UserAccountFormType;
use App\Form\User\UserAnnouncesType;

use App\Form\RegistrationFormType;
use App\Repository\Announces\AnnouncesRequestsRepository;
use App\Repository\AnnouncesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UserRepository;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/mon-compte")
*/
class UserController extends AbstractController{

	private $categoriesRepository;
	private $announcesRepository;
	private $announcesRequestsRepository;
	private $requestStack;


	private $sourcesAddresses;

	public function __construct(
		RequestStack $requestStack,
		CategoriesRepository $categoriesRepository,
		AnnouncesRepository $announcesRepository,
		AnnouncesRequestsRepository $announcesRequestsRepository,
	){

		$this->categoriesRepository = $categoriesRepository;
		$this->announcesRepository = $announcesRepository;
		$this->announcesRequestsRepository = $announcesRequestsRepository;
		$this->requestStack = $requestStack;

	}

	/**
	 * @Route("/", name="account", methods={"POST", "GET"})
	 */
	public function index(Request $request): Response {
		if(!$this->getUser()) return $this->redirectToRoute('login');
		$user = $this->getUser();

		$announcesByUser = $this->announcesRepository->findByUser($user);
		$announcesRequestsByUser = $this->announcesRequestsRepository->findAllRequestsByUser($user);


		return $this->render('front/users/index.html.twig', [
			'announcesByUser' => $announcesByUser,
			'announcesRequestsByUser' => $announcesRequestsByUser,
		]);
	}


	/**
	 * @Route("/activer-desactiver-annonce", name="user_announce_change_active", methods={"POST", "GET"})
	 */
	public function changeActive(Request $request): JsonResponse {

		$id = $request->request->get('id');
		$user = $this->getUser();

		$announce = $this->announcesRepository->find($id);
		if(!$announce) return new JsonResponse(['success' => false]);
		if(!$user) return new JsonResponse(['success' => false]);

		$author = $announce->getUser();
		if($author->getId() != $user->getId()) return new JsonResponse(['success' => false]);

		$state = $announce->getIsActive();
		if($state == false){
			// envoyer mail pour demande de validation à l'Administration
			return new JsonResponse(['success' => false]);
		}

		$announce->setIsActive(false);
		
		$em = $this->getDoctrine()->getManager();
		$em->persist($announce);
		$em->flush();

		return new JsonResponse(['success' => true]);
	}


	/**
	 * @Route("/recuperer-informations-annonce", name="user_get_announce_informations", methods={"POST", "GET"})
	 */
	public function getInformations(Request $request): JsonResponse {

		$id = $request->request->get('id');
		$user = $this->getUser();

		$announce = $this->announcesRepository->find($id);
		if(!$announce) return new JsonResponse(['success' => false]);
		if(!$user) return new JsonResponse(['success' => false]);

		$author = $announce->getUser();
		if($author->getId() != $user->getId()) return new JsonResponse(['success' => false]);



		$formEdit = $this->createForm(UserAnnouncesType::class, $announce, ['method' => 'post', 'action' => $this->generateUrl('user_edit_announce_informations')]);
		$content = $this->render('front/users/edit_announce.html.twig', [
			'formEdit' => $formEdit->createView(),
			'announce' => $announce,
		]);

		$data = [
			'title' => $announce->getTitle(),
			'content' => $content->getContent(),
		];

		return new JsonResponse(['success' => true, 'data' => $data]);
	}


	/**
	 * @Route("/editer-informations-annonce", name="user_edit_announce_informations", methods={"POST", "GET"})
	 */
	public function editInformations(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$form = $request->request->get('user_announces');
		//$announceId = $form['id'];
		$announceId = $request->request->get('announceId');
		$user = $this->getUser();

		$announce = $this->announcesRepository->find($announceId);
		if(!$announce) return new JsonResponse(['success' => false]);
		if(!$user) return new JsonResponse(['success' => false]);

		$author = $announce->getUser();
		if($author->getId() != $user->getId()) return new JsonResponse(['success' => false]);

		$formEdit = $this->createForm(UserAnnouncesType::class, $announce, ['method' => 'post', 'action' => $this->generateUrl('user_edit_announce_informations')]);
		$formEdit->handleRequest($request);

		if($formEdit->isSubmitted() && $formEdit->isValid()){
			if($announce->getCategories()[0]->getId() != $form['category']){
				$category = $this->categoriesRepository->find($form['category']);
				$announce->removeCategory($announce->getCategories()[0]);
				$announce->addCategory($category);
			}
	
			$announcesMetas = $announce->getAnnouncesMetas();
			$metas = $announcesMetas->getMetas();
			$isOfferOrProposal = $metas['isOfferOrProposal'];
			if($isOfferOrProposal != $form['offer']){
				$metas['isOfferOrProposal'] = $form['offer'];
				$announcesMetas->setMetas($metas);
				$em->persist($announcesMetas);
				
			}

			$announce->setIsActive(false);
			$em->persist($announce);
	
			$em->flush();
		}

		return $this->redirectToRoute('account');

	}



		/**
	 * @Route("/recuperer-informations-annonce-reponse", name="user_get_announce_request_information", methods={"POST", "GET"})
	 */
	public function getAnnounceRequest(Request $request): JsonResponse {

		$id = $request->request->get('id');
		$user = $this->getUser();

		$announceRequest = $this->announcesRequestsRepository->find($id);
		if(!$announceRequest) return new JsonResponse(['success' => false]);
		if(!$user) return new JsonResponse(['success' => false]);

		$author = $announceRequest->getAnnounce()->getUser();
		if($author->getId() != $user->getId()) return new JsonResponse(['success' => false]);



		$data = $announceRequest->getData();
		$name = $data['name'] . " " . $data['firstname'];
		$content = $this->render('front/users/show_announce_request.html.twig', [
			'announceRequest' => $announceRequest,
		]);

		$data = [
			'content' => $content->getContent(),
			'name' => $name
		];

		return new JsonResponse(['success' => true, 'data' => $data]);
	}



}
