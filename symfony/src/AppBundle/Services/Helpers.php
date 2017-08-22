<?php
/**
 * Created by PhpStorm.
 * User: danielgbullido
 * Date: 10/8/17
 * Time: 11:17
 */

namespace AppBundle\Services;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class Helpers
{
    public $jwtAuth;

    public function __construct($jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    public function authCheck($hash, $getIdnetity = false)
    {
        $jwtAuth = $this->jwtAuth;
        $auth = false;
        if ($hash != null) {
            if ($getIdnetity == false) {
                $checkToken = $jwtAuth->checkToken($hash);
                if ($checkToken == true) {
                    $auth = true;
                }
            } else {
                $checkToken = $jwtAuth->checkToken($hash, true);
                if (is_object($checkToken)) {
                    $auth = $checkToken;
                }
            }
        }

        return $auth;
    }

    public function json($data)
    {
        $normalize = array(new GetSetMethodNormalizer());
        $encoder = array("json" => new JsonEncoder());

        $serialize = new Serializer($normalize, $encoder);
        $json = $serialize->serialize($data, 'json');

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($json);
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }
}