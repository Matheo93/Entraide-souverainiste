<?php
namespace App\Service;

use App\Entity\Mails\Mails;
use App\Entity\User;
use App\Repository\AnnouncesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\Pages\PagesRepository;
use App\Repository\Settings\CitiesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class MainService {
	private $userRepository;
	private $citiesRepository;
	private $pagesRepository;
	private $categoriesRepository;
	private $announcesRepository;
	private $router;
	private $slugger;

	private $requestStack;
	private $container;
	private $mailer;
	private $em;

	private $twig;



	public function __construct(
		UserRepository $userRepository,
		PagesRepository $pagesRepository,
		CitiesRepository $citiesRepository,
		CategoriesRepository $categoriesRepository,
		AnnouncesRepository $announcesRepository,
		UrlGeneratorInterface $router,
		SluggerInterface $slugger,
		MailerInterface $mailer,
		RequestStack $requestStack,
		EntityManagerInterface $em,
		ContainerInterface $container,
		Environment $twig
	){
		$this->userRepository = $userRepository;
		$this->pagesRepository = $pagesRepository;
		$this->citiesRepository = $citiesRepository;
		$this->announcesRepository = $announcesRepository;
		$this->categoriesRepository = $categoriesRepository;
		$this->router = $router;
		$this->slugger = $slugger;

		$this->mailer = $mailer;
		$this->requestStack = $requestStack;
		$this->container = $container;
		$this->em = $em;

		$this->twig = $twig;
	}

	
	public function getHomeURL(){
	}





	public function sendMail($receiver, $subject, $content_path, $vars, $attachments = []){
		$request = $this->requestStack->getCurrentRequest();
		$parse = parse_url($request->server->get('HTTP_REFERER'));
		$vars['homeUrl'] = $parse['scheme']. "://".$parse['host'];


		//$content = $this->twig->render($content_path, $vars)->getContent();
		$content = $this->twig->render($content_path, $vars);
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

		$em = $this->em;
		$em->persist($newMail);
		$em->flush();

		return $newMail;

	}


	public function slugifier($text){
		return $this->slugger->slug(strtolower($text));
	}


	public function getAnnonceBySlugs($categorySlug, $slug){
		$category = $this->categoriesRepository->findOneBySlug($categorySlug);
        if(!$category) return false;
        $categoryId = $category->getId();

        $annoncesNotCategories = $this->announcesRepository->findBy(['slug' => $slug, "isActive" => 1]);
        $annonce = null;
        foreach($annoncesNotCategories as $annonceNC){
            $annonceNcCats = $annonceNC->getCategories();
            foreach($annonceNcCats as $c){
                if($c->getId() == $categoryId) $annonce = $annonceNC;
            }
        }

		return $annonce;
	}


	public function getAllLocations(){
		$locations = $this->citiesRepository->findBy([], ['name' => 'ASC']);
		return $locations;
	}

	public function getAddress($name, $cp){
		$location = null;

		if(preg_match('/[A-Z]/', $name)){
			$name = $this->slugger->slug(strtolower($name));
		}
		$location = $this->citiesRepository->findOneBy(['code' => $cp, 'slug' => $name]);
		if($location){
			$latlng = $location->getCoords();
			$latlngArr = explode(',', $latlng);
			$location->lat = $latlngArr[0];
			$location->lng = $latlngArr[1];
		}

		return $location;
	}

	public function guessAddress($name = null, $cp = null){
		$location = null;
		if($name != null && $cp == null){
			$name = $this->slugger->slug(strtolower($name));
			$location = $this->citiesRepository->findOneBySlug($name);
		}
		elseif($name == null && $cp != null){
			$location = $this->citiesRepository->findOneByCode($cp);
		}

		if($location){
			$latlng = $location->getCoords();
			$latlngArr = explode(',', $latlng);
			$location->lat = $latlngArr[0];
			$location->lng = $latlngArr[1];
		}

		return $location;
	}

	public function calculateDistanceFromPoints($p1, $p2){
		$lat1 = $p1['lat'];
		$lon1 = $p1['lon'];
		$lat2 = $p2['lat'];
		$lon2 = $p2['lon'];

		$R = 6371e3; // en metres
		$φ1 = $lat1 * PI()/180;
		$φ2 = $lat2 * PI()/180;
		$Δφ = ($lat2-$lat1) * PI()/180;
		$Δλ = ($lon2-$lon1) * PI()/180;

		$p = sin($Δφ/2) * sin($Δφ/2) + cos($φ1) * cos($φ2) * sin($Δλ/2) * sin($Δλ/2);
		$c = 2 * atan2(sqrt($p), sqrt(1-$p));

		$d = $R * $c;
	
		return round($d/1000);
	}

	public function getPage($id, $type = "title"){
		$page = $this->pagesRepository->find($id);

		if(!$page) return null;

		$return = null;
		switch(true){
			case $type == "title":
				$return = ['title' => $page->getTitle()];
				break;
			case $type == "slug":
				$return = ['slug' => $page->getSlug()];
				break;
		}
		return $return;
	}


	public function sliceContent($content){

		$limit = 125;


		return utf8_encode($content);
	}


}