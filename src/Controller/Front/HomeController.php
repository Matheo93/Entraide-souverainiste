<?php

namespace App\Controller\Front;

use App\Entity\ContactForm;
use App\Security\EmailVerifier;

use App\Entity\User;


use App\Form\SearchType;
use App\Form\ContactType;
use App\Repository\AnnouncesRepository;
use App\Repository\CategoriesRepository;
use App\Service\MainService;


use App\Repository\Pages\PagesRepository;
use App\Repository\Settings\CitiesRepository;

use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\String\Slugger\SluggerInterface;

class HomeController extends AbstractController {
	private $emailVerifier;
	private $pagesRepository;
	private $announcesRepository;
	private $categoriesRepository;
	private $citiesRepository;
	private $mainService;
	private $httpClientInterface;

	private $slugger;

	

	public function __construct(
			CitiesRepository $citiesRepository,
			AnnouncesRepository $announcesRepository,
			CategoriesRepository $categoriesRepository,
			PagesRepository $pagesRepository,
			EmailVerifier $emailVerifier,
			MainService $mainService,
			SluggerInterface $slugger,
			HttpClientInterface $httpClientInterface
		){
		$this->emailVerifier = $emailVerifier;
		$this->pagesRepository = $pagesRepository;
		$this->mainService = $mainService;
		$this->httpClientInterface = $httpClientInterface;
		$this->citiesRepository = $citiesRepository;
		$this->announcesRepository = $announcesRepository;
		$this->categoriesRepository = $categoriesRepository;
		$this->slugger = $slugger;

	}
	/**
	* @Route("/", name="home")
	*/
	public function index(Request $request): Response {

		$form = $this->createForm(SearchType::class, null, []);


		$params = $this->filtersSection($request);

		$params['form'] = $form->createView();
		$params['isLocationsNeeded'] = true;


		return $this->render('front/pages/home/home.html.twig', $params);
	}

	public function filtersSection($request){
		$filters = $this->categoriesRepository->findBy([],  ['name' => 'ASC']);

		$isPost = $request->getMethod() == "POST";
		$isRemote = false;
		$localisation = null;
		$searchTerm = null;
		$category = null;
		if($isPost){
			$announces  = $this->announcesRepository->findBy(['isActive' => true], ['dateAdded' => 'DESC']);
			if($request->request->get('searchForm')){
				$searchForm = $request->request->get('searchForm');
				foreach($searchForm as $name => $input){
					switch($name){
						case 'search':
							$searchTerm = $input;
							break;
						case 'isRemote':
							$isRemote = true;
							break;
						case 'localisation':
							$localisation = $input;
							break;
						case 'category':
							$category = $this->categoriesRepository->findOneBySlug($input);
							break;
						default:
							break;
					}
				}

				if($searchTerm || $isRemote || $localisation || $category){
					$announces = $this->announcesRepository->getByTerms($searchTerm, $category, $isRemote);
				}

			}
		}
		else{
			$announces  = $this->announcesRepository->findBy(['isActive' => true], ['dateAdded' => 'DESC']);
		}

		$searchTerms = [];
		$searchTerms['searchTerms'] = $searchTerm;
		$searchTerms['localisation'] = $localisation;
		$searchTerms['isRemote'] = $isRemote;
		$searchTerms['categoryId'] = $category ? $category->getId() : null;

		foreach($announces as $announce){
			$announcesMetas = $announce->getAnnouncesMetas();
			$announce->isOfferOrProposal = $announcesMetas ? $announcesMetas->getMetas()['isOfferOrProposal'] : null;	
		}

		return [
			'filters' => $filters,
			'announces' => $announces,
			'isLocationsNeeded' => true,
			'searchTerms' => $searchTerms
		];
	}



	



	/**
	* @Route("/contactez-nous", name="contact", methods={"GET","POST"})
	*/
	public function contactPage(Request $request){

		$contact = new ContactForm();

		$contactForm = $this->createForm(ContactType::class, $contact, [
			'action' => $this->generateUrl('contact'),
			'method' => 'POST'
		]);
		$contactForm->handleRequest($request);

		if($contactForm->isSubmitted() && $contactForm->isValid()){


			$data = $request->request->get('contact');
			$d = ['name' => $data['name'], 'firstname' => $data['firstname'],  'message' => $data['message'], "email" => $data['email'], 'phone' => $data['phone']];
			$receiver = $_ENV['WEBMASTER_CONTACT_EMAIL'];
			$subject = "Nouvelle entrée du formulaire de contact - Entraide Souverainiste";
			$content_path = "/mails/front/contact/new.html.twig";
			$vars = $d;
			$sendMail = $this->mainService->sendMail($receiver, $subject, $content_path, $vars, []);

			$contact->setData($d);
			$em = $this->getDoctrine()->getManager();
			$em->persist($contact);
			$em->flush();
			$this->addFlash('success', "Votre demande de contact a bien été envoyée. Merci de votre contribution, nous vous recontacterons au plus vite.");

			

		}
		return $this->render('front/pages/contact/contact.html.twig', [
			'contactForm' => $contactForm->createView(),
		]);
	}

}