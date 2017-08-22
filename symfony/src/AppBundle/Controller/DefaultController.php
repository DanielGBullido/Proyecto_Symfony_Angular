<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ]);
    }


    public function loginAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $jwtAuth = $this->get("app.jwtAuth");

        $json = $request->get("json", null);

        if ($json != null) {
            $params = json_decode($json);
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : false;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email not valid!!";

            $validateEmail = $this->get("validator")->validate($email, $emailConstraint);
            $pwd = hash("sha256", $password);

            if (count($validateEmail) == 0 && $password != null) {
                $singUp = $jwtAuth->singUp($email, $pwd, $getHash);
                return new JsonResponse($singUp);
            } else {
                return $helpers->json(
                    array(
                        "status" => "error",
                        "data" => "login not valid!!"
                    ));
            }

        } else {
            return $helpers->json(
                array(
                    "status" => "error",
                    "data" => "send! json with post"
                ));
        }
    }

    public function pruebaAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $check = $helpers->authCheck($hash);

        var_dump($check);die;

        /*
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository("BackendBundle:User")->findAll();
*/
        return $helpers->json($users);
    }
}
