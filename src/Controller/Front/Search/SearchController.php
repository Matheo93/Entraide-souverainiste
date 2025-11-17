<?php

namespace App\Controller\Front\Search;

use App\Entity\Settings\Cities;
use App\Service\MainService;


use App\Repository\AnnouncesRepository;
use App\Repository\Stats\StatsRepository;
use App\Repository\Stats\StatsTypeRepository;
use App\Repository\CategoriesRepository;
use App\Repository\Settings\CitiesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/rechercher")
*/
class SearchController extends AbstractController
{
	private $slugger;
	private $statsRepository;
	private $statsTypeRepository;
	private $categoriesRepository;
	private $announcesRepository;
	private $citiesRepository;

	private $mainService;

	private $sourcesAddresses;
	public function __construct(CitiesRepository $citiesRepository,StatsRepository $statsRepository,StatsTypeRepository $statsTypeRepository,SluggerInterface $slugger, ContainerInterface $container, MainService $mainService,CategoriesRepository $categoriesRepository,AnnouncesRepository $announcesRepository
		){
		$this->statsRepository = $statsRepository;
		$this->statsTypeRepository = $statsTypeRepository;

		$this->mainService = $mainService;


		$this->slugger = $slugger;
		$this->container = $container;

		//$this->sourcesAddresses = json_decode(file_get_contents($this->container->getParameter('list_communes_france')));
		$this->categoriesRepository = $categoriesRepository;
		$this->announcesRepository = $announcesRepository;
		$this->citiesRepository = $citiesRepository;

		
	}

	/**
	* @Route("/", name="search", methods={"POST", "GET"})
	*/
	public function index(Request $request){

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
		return $this->render('front/pages/search/search.html.twig', [
			'filters' => $filters,
			'announces' => $announces,
			'isLocationsNeeded' => true,
			'searchTerms' => $searchTerms
		]);
	}

	
}