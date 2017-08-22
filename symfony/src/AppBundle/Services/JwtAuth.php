<?php
/**
 * Created by PhpStorm.
 * User: danielgbullido
 * Date: 10/8/17
 * Time: 13:08
 */

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth
{
    public $manager;
    public $key;

    public function __construct($manager)
    {
        $this->manager = $manager;
        $this->key = "clave-secreta";
    }

    public function singUp($email, $password, $getHash = false)
    {
        $key = $this->key;
        $user = $this->manager->getRepository('BackendBundle:User')->findOneBy(
            array(
                "email" => $email,
                "password" => $password,
            )
        );
        $signUp = false;
        if (is_object($user)) {
            $signUp = true;
        }

        if ($signUp == true) {
            $token = array(
                "sub" => $user->getId(),
                "email" => $user->getEmail(),
                "name" => $user->getName(),
                "surname" => $user->getSurname(),
                "password" => $user->getPassword(),
                "image" => $user->getImage(),
                "iat" => time(),
                "exp" => time() + (7 * 24 * 60 * 60),
            );

            $jwt = JWT::encode($token, $key, "HS256");
            $decoded = JWT::decode($jwt, $key, array("HS256"));

            if ($getHash) {
                return $jwt;
            } else {
                return $decoded;
            }

            return array("status" => "error", "data" => "login success!!");
        } else {
            return array("status" => "error", "data" => "login failed!!");
        }
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $key = $this->key;
        $auth = false;

        try {
            $decoded = JWT::decode($jwt, $key, array("HS256"));
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity == true) {
            return $decoded;
        } else {
            return $auth;
        }
    }
}