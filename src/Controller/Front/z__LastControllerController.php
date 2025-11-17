<?php

namespace App\Controller\Front;

use App\Repository\AnnouncesRepository;
use App\Repository\Pages\PagesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class z__LastControllerController extends AbstractController {

	private $pagesRepository;
	public function __construct(PagesRepository $pagesRepository, private AnnouncesRepository $announcesRepository){
		$this->pagesRepository = $pagesRepository;
	}




	
	/**
	 * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
	 * @param Request $request
	 * @return Response
	 */
	public function sitemap(Request $request): Response {

		$listDefaultRoutes = ['home', 'create_announce', 'search'];

		$pages = $this->pagesRepository->findAll();
		$hostname = $request->getSchemeAndHttpHost();
		$mapsPages = [];
		$mapsPages[] = ['url' => $this->generateUrl('app_login')];

		$mapsPages[] = ['url' => $this->generateUrl('contact')];


		$announces = $this->announcesRepository->findBy(['isActive' => true]);

		foreach($listDefaultRoutes as $route){
			$mapsPages[] = ['url' => $this->generateUrl($route)];
		}
	
		foreach($announces as $announce){
			$mapsPages[] = ['url' => $this->generateUrl('get_annonce', [
				'categorySlug' => $announce->getCategories()[0]->getSlug(),
				'slug' => $announce->getSlug(),
			])];
		}
		foreach($pages as $page){
			$slug = $page->getSlug();
			if($slug == "home") continue;
			$mapsPages[] = ['url' => $this->generateUrl('get_page', ['slug' => $slug]), 'lastmod' => $page->getUpdatedAt()->format('Y-m-d')];
		}


		$r = new Response($this->renderView('front/sitemap/index.html.twig', ['mapsPages' => $mapsPages, 'host' => $hostname, ]));
		$r->headers->set('Content-Type', 'text/xml');

		return $r;
	}



    /**
     * @Route("/{slug}", name="get_page")
     */
	public function searchPageSlug($slug, Request $request): Response {
		if(isset($slug) && strlen($slug) > 0){
			$page = $this->pagesRepository->findBySlug($slug);
			if($page){
				$slug = $page->getSlug();
				if($slug == "home") return $this->redirectToRoute('home');

				$id = $page->getId();
				$templatePath = 'front/pages/get_page.html.twig';
				switch(true){
					case $id == 2:
						$templatePath = 'front/pages/qui-sommes-nous.html.twig';
						break;
					case $id == 10:
						$templatePath = 'front/pages/le-guide.html.twig';
						break;
				}
				return $this->render($templatePath, [
					'page' => $page,
					"whiteTheme" => true,
				]);
			}
			else{
				if($slug == 'cgu'){
					$cgv = $this->pagesRepository->find(12);
					return $this->redirectToRoute('get_page', ['slug' => $cgv->getSlug()]);
				}

				return $this->get404();
				//return new Response($this->render('front/parts/404.html.twig'), 404);
			}
		}

	}





	public function get404(){
		//return new Response('', 404);
		throw $this->createNotFoundException('La page n\'existe pas.');

		//return $this->render('front/parts/404.html.twig');
	}

}
		