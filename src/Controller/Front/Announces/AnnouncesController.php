<?php
namespace App\Controller\Front\Announces;

use App\Entity\Announces\Announces;
use App\Entity\Announces\AnnouncesMetas;
use App\Entity\Announces\AnnouncesRequests;
use App\Entity\User;
use App\Form\AnnouncesType;
use App\Repository\Announces\AnnouncesRequestsRepository;
use App\Repository\AnnouncesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UserRepository;
use App\Service\MainService;
use App\Service\DiscordModerationService;
use App\Service\RecaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

#[Route('/')]

class AnnouncesController extends AbstractController {
    
    private $userRepository;
    private $categoriesRepository;
    private $announcesRepository;
    private $announcesRequestsRepository;
    private $slugger;
    private $userPasswordEncoder;
    private $mainService;
    private $discordModerationService;
    private $recaptchaService;


    public function __construct(
        UserRepository $userRepository,
        CategoriesRepository $categoriesRepository,
        AnnouncesRequestsRepository $announcesRequestsRepository,
        AnnouncesRepository $announcesRepository,
        SluggerInterface $slugger,
        UserPasswordEncoderInterface $userPasswordEncoder,
        MainService $mainService,
        DiscordModerationService $discordModerationService,
        RecaptchaService $recaptchaService
    ){
        $this->userRepository = $userRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->announcesRequestsRepository = $announcesRequestsRepository;
        $this->announcesRepository = $announcesRepository;
        $this->slugger = $slugger;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->mainService = $mainService;
        $this->discordModerationService = $discordModerationService;
        $this->recaptchaService = $recaptchaService;

    }


	/**
	* @Route("/creer-son-annonce", name="create_announce", methods={"GET","POST"})
	*/
    public function create(Request $request) : Response{
        $announce = new Announces();
        $form = $this->createForm(AnnouncesType::class, $announce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier le reCAPTCHA
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            if (!$this->recaptchaService->verify($recaptchaResponse, $request->getClientIp())) {
                $this->addFlash('error', 'Veuillez valider le captcha de sécurité.');
                return $this->render('front/announces/add.html.twig', [
                    'announce' => $announce,
                    'form' => $form->createView(),
                ]);
            }

            $em = $this->getDoctrine()->getManager();

            $requestAnnounce  = $request->request->get('announces');

            $category = $this->categoriesRepository->find($requestAnnounce['category']);
            $announce->addCategory($category);

            $categorySlug = $category->getSlug();
            /*switch($categorySlug){
                case 'emploi-interim':
                    $isOfferOrProposal = $requestAnnounce['offer'];
                    $announcesMetas = new AnnouncesMetas();
                    $announcesMetas->setAnnounce($announce);
                    $announcesMetas->setMetas(['isOfferOrProposal' => $isOfferOrProposal]);
                    $em->persist($announcesMetas);
                    break;
            }*/

            $isOfferOrProposal = $requestAnnounce['offer'];
            $announcesMetas = new AnnouncesMetas();
            $announcesMetas->setAnnounce($announce);
            $announcesMetas->setMetas(['isOfferOrProposal' => $isOfferOrProposal]);
            $em->persist($announcesMetas);
            

            $slugTitle = $this->slugger->slug(strtolower($announce->getTitle()));

            // VERIFIER SI SLUG EXISTE DEJA AVEC SLUG + CATEGORY / SI EXISTE METTRE "-2..."
            //$isThereAnyOtherAnnonce = $this->announcesRepository->findOneBy(['slug' => $slugTitle, ''])

            $announce->setSlug($slugTitle);


            $user = $this->getUser();
            if($user){
                $announce->setUser($user);
            }
            else{
                $email = $request->request->get('announces')['email'];
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $this->render('front/announces/add.html.twig', [
                    'announce' => $announce,
                    'form' => $form->createView(),
                    'error' => 'Email invalide'
                ]);
                $userExisted = $this->userRepository->findOneByEmail($email);
                if($userExisted){
                    $announce->setUser($userExisted);
                }
                else{
                    $user = new User();
                    ## MODIFIER LE SALT
                    $rawPassword = crypt(rand(1, 99999), 'SEL');
                    $user->setEmail($email);
                    $user->setReferedId(0);
                    $user->setPassword(
                        $this->userPasswordEncoder->encodePassword(
                            $user,
                            $rawPassword
                        )
                    );
                    $user->setRoles(["ROLE_USER"]);
                    $em->persist($user);

                    $announce->setUser($user);
                    //$this->mainService->sendMail($email, 'Votre inscription au service d\'Action Sociale', '/mails/front/registration/auto-registration.html.twig', ['rawPassword' => $rawPassword]);
                }
            }

            $em->persist($announce);
            $em->flush();

            // Send to Discord for moderation (if webhook configured)
            try {
                $this->discordModerationService->sendForModeration($announce);
            } catch (\Exception $e) {
                // Log error but don't block announce creation
                error_log('Discord moderation failed: ' . $e->getMessage());
            }

            return $this->redirectToRoute('get_annonce', ['categorySlug' => $category->getSlug(), 'slug' => $announce->getSlug()]);
        }

        return $this->render('front/announces/add.html.twig', [
            'announce' => $announce,
            'form' => $form->createView(),
        ]);
    }


    /**
	* @Route("/annonces/{categorySlug}/{slug}", name="get_annonce", methods={"GET","POST"})
	*/
    public function getAnnonce(Request $request, $categorySlug, $slug) : Response{

        $annonce = $this->mainService->getAnnonceBySlugs($categorySlug, $slug);

        if(!$annonce) return $this->redirectToRoute('home');

        
        $announcesMetas = $annonce->getAnnouncesMetas();
        $annonce->isOfferOrProposal = $announcesMetas ? $announcesMetas->getMetas()['isOfferOrProposal'] : null;

        return $this->render('front/announces/show.html.twig', [
            'announce' => $annonce,
        ]);
    }



    public function annonceNotFound(){

        return $this->render('front/announces/not_found.html.twig', [
        ]);
    }


    /**
	* @Route("/annonces/{categorySlug}/{slug}/contactez-le-redacteur", name="announce_contact_origin", methods={"POST"})
	*/
    public function createAnnounceContact(Request $request, $categorySlug, $slug){

        $announce = $this->mainService->getAnnonceBySlugs($categorySlug, $slug);

        if($announce == false) return $this->redirectToRoute('search');

        $user = $this->getUser();
        $isUserConnected = !$user ? false : true;
        $form = $request->request;

        $userEmail = $isUserConnected ? $user->getEmail() : $form->get('email');

        $isAlreadyContact = $this->announcesRequestsRepository->findOneBy(['announce' => $announce, 'email' => $userEmail]);

        if($isAlreadyContact) return $this->redirectToRoute('account');

        $announceRequest = new AnnouncesRequests();
        $announceRequest->setAnnounce($announce);
        $announceRequest->setEmail($userEmail);

        $data = [];
        $data['email'] = $userEmail;
        $data['name'] = $form->get('name');
        $data['firstname'] = $form->get('firstname');
        $data['message'] = $form->get('message');
        $announceRequest->setData($data);

        $em = $this->getDoctrine()->getManager();
        $em->persist($announceRequest);
        $em->flush();

        //$receiver = $this->getParameter('admin_contact_email');
        $receiver = $announce->getUser()->getEmail();
		$subject = "Réponse à votre annonce " . $announce->getTitle();
		$content_path = "/mails/front/requests/contact.html.twig";
		$vars = ['name' => $data['name'], 'firstname' => $data['firstname'],  'message' => $data['message'], "email" => $userEmail, 'announceTitle' => $announce->getTitle()];

	    $sendMail = $this->mainService->sendMail($receiver, $subject, $content_path, $vars, []);
        

        return $this->redirectToRoute('account');
    }

}

?>