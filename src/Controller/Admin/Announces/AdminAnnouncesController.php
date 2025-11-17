<?php

namespace App\Controller\Admin\Announces;

use App\Entity\Announces\Announces;
use App\Security\EmailVerifier;

use App\Entity\User;
use App\Entity\Mails\Mails;


use App\Form\Admin\Announce\AdminAnnounceFormType;

use App\Form\Admin\Users\AdminUserType;
use App\Repository\Announces\AnnouncesRequestsRepository;
use App\Repository\AnnouncesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Service\MainService;




/**
 * @Route("/admin/annonces")
 */

class AdminAnnouncesController extends AbstractController
{
    private $slugger;
	private $userRepository;
	private $announcesRepository;
	private $announcesRequestsRepository;
	private $categoriesRepository;
	private $emailVerifier;

	private $mainService;

	public function __construct(
		SluggerInterface $slugger,
		UserRepository $userRepository,
		AnnouncesRepository $announcesRepository,
		AnnouncesRequestsRepository $announcesRequestsRepository,
		CategoriesRepository $categoriesRepository,
		EmailVerifier $emailVerifier,
		MainService $mainService,
	){
        $this->slugger = $slugger;
        $this->userRepository = $userRepository;
        $this->announcesRepository = $announcesRepository;
        $this->announcesRequestsRepository = $announcesRequestsRepository;
        $this->categoriesRepository = $categoriesRepository;
		$this->emailVerifier = $emailVerifier;

		$this->mainService = $mainService;

	}
	/**
	 * @Route("/", name="admin_announces", methods={"GET"})
	 */
	public function index()
	{
		$announces = $this->announcesRepository->findBy([], ['dateAdded' => 'DESC']);
		return $this->render('admin/announces/index.html.twig', [
			'announces' =>  $announces,
		]);
	}

	/**
	 * @Route("/{id}/modifier", name="admin_announces_edit", methods={"GET","POST"})
	 */
	public function edit(Request $request, Announces $announce): Response {
		$compact = [];

		$formAnnounce = $this->createForm(AdminAnnounceFormType::class, $announce);
		$formAnnounce->handleRequest($request);

		$compact['announce'] = $announce;
		$compact['formAnnounce'] = $formAnnounce->createView();
		$compact['locations_source'] = $this->mainService->getAllLocations();

		if ($formAnnounce->isSubmitted() && $formAnnounce->isValid()) {
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('admin_announces');
		}

		return $this->render('admin/announces/edit.html.twig', $compact);
	}


	



	
	/**
	 * @Route("/exporter-liste-annonces", name="admin_announces_export_table")
	 * @param Request $request
	 * @return JsonResponse
	*/
	public function exportAnnouncesTableToExcel(Request $request){
		$htmlTable = "<table><thead><tr><th>ID</th><th>Utilisateur</th><th>Titre</th><th>Contenu</th><th>Catégorie</th><th>Actif</th><th>Posté le</th></tr></thead><tbody>";
		$announces = $this->announcesRepository->findAll();
		foreach($announces as $announce){
			$categoryName = "";
			if(count($announce->getCategories()) > 0) $categoryName =$announce->getCategories()[0]->getName();
			$isActive = $announce->getIsActive() == true ? 'oui' : 'non';

			$user = "";
			if($announce->getUser() != null) $user = $announce->getUser()->getEmail();
			$htmlTable .= "<tr><td>".$announce->getId()."</td><td>$user</td><td>".$announce->getTitle()."</td><td>".strip_tags(nl2br($announce->getContent()))."</td><td>".$categoryName."</td><td>".$isActive."</td><td>". $announce->getDateAdded()->format('d/m/Y H:i') ."</td></tr>";
		
		}
		$htmlTable .= "</tbody></table>";



		$reader = new Html();
		$spreadsheet = $reader->loadFromString($htmlTable);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);

		$now = date('d-m-Y H-i');
		$fileName = 'liste-annonces-actionsociale-'.$now.'.xls';
		$writer->save($fileName);


		return new JsonResponse(['success' => true, 'data' => ['name' => $fileName]]);
	}




	/**
	 * @Route("/{id}", name="admin_announces_delete", methods={"DELETE"})
	 */
	public function delete(Request $request, Announces $announce): Response
	{
		if ($this->isCsrfTokenValid('delete'.$announce->getId(), $request->request->get('_token'))) {
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->remove($announce);
			$entityManager->flush();
		}

		return $this->redirectToRoute('admin_announces');
	}


		/**
	 * @Route("/reponses", name="admin_announces_requests", methods={"GET"})
	 */
	public function getAllAnnoncesRequests()
	{
		$announcesRequests = $this->announcesRequestsRepository->findBy([], ['registeredAt' => 'DESC']);
		return $this->render('admin/announces_requests/index.html.twig', [
			'announcesRequests' =>  $announcesRequests,
		]);
	}

		/**
	 * @Route("/exporter-liste-annonces-reponses", name="admin_announces_requests_export_table")
	 * @param Request $request
	 * @return JsonResponse
	*/
	public function exportAnnouncesRequestsTableToExcel(Request $request){
		$htmlTable = "<table><thead><tr><th>ID</th><th>Utilisateur rédigeant</th><th>Titre</th><th>Contenu</th><th>Catégorie</th><th>Actif</th><th>Utilisateur répondant</th><th>Posté le</th></tr></thead><tbody>";
		$announcesRequests = $this->announcesRequestsRepository->findAll();
		foreach($announcesRequests as $announcesRequest){
			$announce = $announcesRequest->getAnnounce();
			$categoryName = "";
			if(count($announce->getCategories()) > 0) $categoryName =$announce->getCategories()[0]->getName();
			$isActive = $announce->getIsActive() == true ? 'oui' : 'non';

			$user = "";
			if($announce->getUser() != null) $user = $announce->getUser()->getEmail();

			$emailRequest = $announcesRequest->getEmail();
			$htmlTable .= "<tr><td>".$announce->getId()."</td><td>$user</td><td>".$announce->getTitle()."</td><td>".strip_tags(nl2br($announce->getContent()))."</td><td>".$categoryName."</td><td>".$isActive."</td><td>$emailRequest</td><td>". $announcesRequest->getRegisteredAt()->format('d/m/Y H:i') ."</td></tr>";
		
		}
		$htmlTable .= "</tbody></table>";



		$reader = new Html();
		$spreadsheet = $reader->loadFromString($htmlTable);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);

		$now = date('d-m-Y H-i');
		$fileName = 'liste-reponses-annonces-actionsociale-'.$now.'.xls';
		$writer->save($fileName);


		return new JsonResponse(['success' => true, 'data' => ['name' => $fileName]]);
	}

	/**
	 * @Route("/recuperer-informations-annonces-reponses", name="admin_announces_requests_get_infos")
	 * @param Request $request
	 * @return JsonResponse
	*/
	public function getAnnounceRequestInfos(Request $request){
		$announceId = $request->request->get('id');
		$announceRequest = $this->announcesRequestsRepository->find($announceId);
		if(!$announceRequest) return new JsonResponse(['success' => true, 'message' => 'Réponse non trouvée']);

		$dataRaw = $announceRequest->getData();
		$data = [
			'title' => $announceRequest->getAnnounce()->getTitle(),
			'email' => $announceRequest->getEmail(),
			'name' => isset($dataRaw['name']) ? $dataRaw['name'] : '',
			'firstname' => isset($dataRaw['firstname']) ? $dataRaw['firstname'] : '',
			'message' => isset($dataRaw['message']) ? $dataRaw['message'] : '',
		];
		return new JsonResponse(['success' => true, 'data' => $data]);

	}



}

?>