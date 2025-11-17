<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Service\APIService;

class SecurityController extends AbstractController
{
    private $apiService;
    public function __construct(APIService $apiService){
        $this->apiService = $apiService;
    }
    /**
     * @Route("/se-connecter", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('account');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();
		
		if($error){
			$errorMessage = $this->getErrorMessage($error);
			if($errorMessage) $this->addFlash('error', $errorMessage);
		}

        return $this->render('front/registration/register.html.twig', ['last_username' => $lastUsername, 'error' => $error, "whiteTheme" => true]);
	}
	


	public function getErrorMessage($error){
		$message = "Identifiants incorrects";
		$errorMessage = $error->getMessage();
		switch(true){
			case strstr($errorMessage, 'Email could not be found'):
				$message = "Cet Email n'existe pas";
				break;
		}

		return $message;
	}

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
