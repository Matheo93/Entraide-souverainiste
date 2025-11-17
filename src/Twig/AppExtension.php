<?php
namespace App\Twig;


use App\Repository\Pages\PagesRepository;
use App\Repository\Texts\TextsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Security\TokenStorage;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
//use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension {

	private $pagesRepository;
	private $textsRepository;
	public function __construct(PagesRepository $pagesRepository, TextsRepository $textsRepository) {
		$this->pagesRepository = $pagesRepository;
		$this->textsRepository = $textsRepository;

	}



	public function getPageLink($id) {
		$footerLink = $this->pagesRepository->find($id);
		return ($footerLink != null ? $footerLink->getSlug() : "");
	}


	public function getText($id){
		$text = $this->textsRepository->find($id);
		return ($text != null ? $text->getContent() : "");
	}

	public function getFunctions() {
		return [
			new TwigFunction('getPageLink', [$this, 'getPageLink']),
			new TwigFunction('getText', [$this, 'getText']),
		];
	}

}


?>