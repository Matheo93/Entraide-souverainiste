<?php

namespace App\Controller\Admin\Announces;

use App\Entity\Announces\Announces;
use App\Entity\Categories;
use App\Security\EmailVerifier;

use App\Entity\User;
use App\Entity\Mails\Mails;


use App\Form\Admin\Announce\AdminCategoriesFormType;

use App\Form\Admin\Users\AdminUserType;
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
 * @Route("/admin/categories")
 */

class AdminCategoriesController extends AbstractController
{
    private $slugger;
	private $userRepository;
	private $announcesRepository;
	private $categoriesRepository;
	private $emailVerifier;

	private $mainService;

	public function __construct(
		SluggerInterface $slugger,
		UserRepository $userRepository,
		AnnouncesRepository $announcesRepository,
		CategoriesRepository $categoriesRepository,
		EmailVerifier $emailVerifier,
		MainService $mainService,
	){
        $this->slugger = $slugger;
        $this->userRepository = $userRepository;
        $this->announcesRepository = $announcesRepository;
        $this->categoriesRepository = $categoriesRepository;
		$this->emailVerifier = $emailVerifier;

		$this->mainService = $mainService;

	}
	/**
	 * @Route("/", name="admin_categories", methods={"GET"})
	 */
	public function index()
	{
		$categories = $this->categoriesRepository->findBy([], ['name' => 'DESC']);
		return $this->render('admin/categories/index.html.twig', [
			'categories' =>  $categories,
		]);
	}

	/**
	 * @Route("/ajouter", name="admin_category_add", methods={"GET","POST"})
	 */
	public function add(Request $request): Response {
		$compact = [];
		$category = new Categories();

		$formCategory = $this->createForm(AdminCategoriesFormType::class, $category);
		$formCategory->handleRequest($request);

		$compact['category'] = $category;
		$compact['formCategory'] = $formCategory->createView();

		if ($formCategory->isSubmitted() && $formCategory->isValid()) {
			$categorySlugging = $this->slugger->slug($category->getSlug());
			$category->setSlug($categorySlugging);

			$em = $this->getDoctrine()->getManager();
			$em->persist($category);
			$em->flush();

			return $this->redirectToRoute('admin_categories');
		}

		return $this->render('admin/categories/new.html.twig', $compact);
	}



	/**
	 * @Route("/{id}/modifier", name="admin_category_edit", methods={"GET","POST"})
	 */
	public function edit(Request $request, Categories $category): Response {
		$compact = [];

		$formCategory = $this->createForm(AdminCategoriesFormType::class, $category);
		$formCategory->handleRequest($request);

		$compact['category'] = $category;
		$compact['formCategory'] = $formCategory->createView();

		if ($formCategory->isSubmitted() && $formCategory->isValid()) {
			$categorySlugging = $this->slugger->slug($category->getSlug());
			$category->setSlug($categorySlugging);

			$em = $this->getDoctrine()->getManager();
			$em->persist($category);
			$em->flush();

			return $this->redirectToRoute('admin_categories');
		}

		return $this->render('admin/categories/edit.html.twig', $compact);
	}


	


}

?>