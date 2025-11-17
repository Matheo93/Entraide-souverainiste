<?php

namespace App\Controller\Admin\Users;

use App\Security\EmailVerifier;

use App\Entity\User;
use App\Entity\Mails\Mails;


use App\Form\Admin\User\AdminUserFormType;

use App\Form\Admin\Users\AdminUserType;

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
 * @Route("/admin/utilisateurs")
 */

class AdminUsersController extends AbstractController
{
    private $slugger;
	private $userRepository;
	private $emailVerifier;

	private $mainService;

	public function __construct(
		SluggerInterface $slugger,
		UserRepository $userRepository,
		EmailVerifier $emailVerifier,
		MainService $mainService,
	){
        $this->slugger = $slugger;
        $this->userRepository = $userRepository;
		$this->emailVerifier = $emailVerifier;

		$this->mainService = $mainService;

	}
	/**
	 * @Route("/", name="admin_users", methods={"GET"})
	 */
	public function index(UserRepository $usersRepository)
	{
		$users = $usersRepository->findAll();

		return $this->render('admin/users/index.html.twig', [
			'users' =>  $users,
		]);
	}

	/**
	 * @Route("/{id}/modifier", name="admin_user_edit", methods={"GET","POST"})
	 */
	public function edit(Request $request, User $user): Response {
		$compact = [];

		$formUser = $this->createForm(AdminUserFormType::class, $user);
		$formUser->handleRequest($request);

		$compact['user'] = $user;
		$compact['formUser'] = $formUser->createView();
		$compact['locations_source'] = $this->mainService->getAllLocations();

		if ($formUser->isSubmitted() && $formUser->isValid()) {
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('admin_users');
		}

		return $this->render('admin/users/edit.html.twig', $compact);
	}


	



	
	/**
	 * Recupère le tableau listant les commandes
	 * @Route("/exporter-liste-utilisateurs", name="admin_users_export_table")
	 * @param Request $request
	 * @return JsonResponse
	*/
	public function exportUsersTableToExcel(Request $request){
		$htmlTable = "<table><thead><tr><th>ID</th><th>Email</th><th>Inscrit le</th></tr></thead><tbody>";
		$users = $this->userRepository->findAll();
		foreach($users as $user){


			$registeredAt = $user->getRegisteredAt()->format('d/m/Y');
			$htmlTable .= "<tr><td>".$user->getId()."</td><td>".$user->getEmail()."</td><td>". $registeredAt ."</td></tr>";
		
		}
		$htmlTable .= "</tbody></table>";



		$reader = new Html();
		$spreadsheet = $reader->loadFromString($htmlTable);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);

		$now = date('d-m-Y H-i');
		$fileName = 'liste-utilisateurs-actionsociale-'.$now.'.xls';
		// A changer, rajouter un envoi de mail / changer chemin pour rendre non accessible aux utilisateurs le fichier. (actuellement tous les fichiers xls sont enregistrés dans le dossier public pour laisser aux administrateurs la possibilité d'y accéder en HTTP)
		$writer->save($fileName);


		return new JsonResponse(['success' => true, 'data' => ['name' => $fileName]]);
	}





	/**
	 * @Route("/recuperer-formulaire-adresse", name="admin_user_address_get_form_path",  methods={"GET","POST"})
	 */
	public function getNewMailForm(Request $request){
		
	}















	/**
	 * @Route("/{id}", name="admin_user_delete", methods={"DELETE"})
	 */
	public function delete(Request $request, User $user): Response
	{
		if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->remove($user);
			$entityManager->flush();
		}

		return $this->redirectToRoute('admin_users');
	}



	/**
	 * @Route("/approuver-utilisateur-email", name="admin_user_email_approuve", methods={"POST"})
	 */
	public function verifyUserEmail(Request $request){
		$id = $request->request->get('id');
		if(!$id) return new JsonResponse(['success' => false]);
		$user = $this->userRepository->find($id);
		if(!$user) return new JsonResponse(['success' => false]);

		$em = $this->getDoctrine()->getManager();

		$user->setIsVerified(true);
		$em->persist($user);
		$em->flush();

		return new JsonResponse(['success' => true]);
	}


	/**
	 * @Route("/desactiver-utilisateur-email", name="admin_user_email_unapprouve", methods={"POST"})
	 */
	public function unverifyUserEmail(Request $request){
		$id = $request->request->get('id');
		if(!$id) return new JsonResponse(['success' => false]);
		$user = $this->userRepository->find($id);
		if(!$user) return new JsonResponse(['success' => false]);

		$em = $this->getDoctrine()->getManager();

		$user->setIsVerified(false);
		$em->persist($user);
		$em->flush();

		return new JsonResponse(['success' => true]);
	}



	/**
	 * @Route("/set_as_admin/{id}", name="admin_user_add_admin", methods={"POST"})
	 */
	public function setRoleAdmin(Request $request, User $user): Response {
		if ($this->isCsrfTokenValid('add_admin'.$user->getId(), $request->request->get('_token'))) {
			$entityManager = $this->getDoctrine()->getManager();
			$roles = $user->getRoles();
			array_push($roles, 'ROLE_ADMIN');
			$user->setRoles($roles);
			$entityManager->persist($user);
			$entityManager->flush();
			$userTitle = $user->getEmail();
			$this->addFlash('success', $userTitle . " a été ajouté en tant qu'Administrateur.");
		}
		else{
			$this->addFlash('error', 'Il y a eu un problème, l\'utilisateur n\'existe pas.');
		}

		return $this->redirectToRoute('admin_users');
	}


		/**
	 * @Route("/disable_admin/", name="disable_admin_path", methods={"POST"})
	 */
	public function disableAdmin(Request $request) {
		$userId = $request->request->get('id');

		$user = $this->userRepository->find($userId);
		$roles = $user->getRoles();
		foreach($roles as $index => $role){
			if($role == "ROLE_ADMIN") unset($roles[$index]);
		}
		$user->setRoles($roles);


		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

		return new JsonResponse(['success' => true]);

	}
}

?>