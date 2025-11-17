<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class APIService {

    private $baseURL =  "https://cerclearistote.fr";
    private $apiPath = "/wp-json";
    private $key = "8ae8a99278177af631cd81e651e7ef17";

    private $scopes = [
        'distantConnect' => '/distant-connect-actionsociale/v1/login',
    ];


    private $em;


    public function __construct(EntityManagerInterface $em){

        $this->em = $em;
    }

    public function  makeRequest($url, $method = "GET", $params = []){
        switch($method){
            case 'GET':
                break;
            case 'POST':
                break;
        }

        $ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        if($method == "POST"){
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
		$response = curl_exec($ch);
        $data = json_decode($response);


        return $data;
    }


    public function getDistantConnect($params){

        $url = $this->baseURL . $this->apiPath .  $this->scopes['distantConnect'];

        $params['password'] = @openssl_encrypt($params['password'], 'aes-256-cbc', $this->key.":".$params['password']);
        $params['key'] = $this->key;

        $data = $this->makeRequest($url, "POST", $params);

        if($data->success == true){
            return $data->data;
        }
        else return false;

    }
}