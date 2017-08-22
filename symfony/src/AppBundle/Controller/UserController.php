<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;

class UserController extends Controller
{
    public function newAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $json = $request->get("json", null);
        $params = json_decode($json);
        $data = array(
            "status" => "error",
            "code" => 400,
            "msg" => "User not created",
        );

        if ($json != null) {
            $createdAt = new \DateTime("now");
            $image = null;
            $rol = "user";

            $email = (isset($params->email)) ? $params->email : null;
            $name = (isset($params->name)) ? $params->name : null;
            $surname = (isset($params->surname)) ? $params->surname : null;
            $password = (isset($params->password)) ? $params->password : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email not valid!!";
            $validateEmail = $this->get("validator")->validate($email, $emailConstraint);

            if ($email != null && count($validateEmail) == 0 && $password != null && $name != null && $surname != null) {
                $user = new User();
                $user->setCreateAt($createdAt);
                $user->setImage($image);
                $user->setRole($rol);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                $pwd = hash("sha256", $password);
                $user->setPassword($pwd);

                $em = $this->getDoctrine()->getManager();
                $issetUser = $em->getRepository("BackendBundle:User")->findBy(
                    array(
                        "email" => $email
                    )
                );
                if (count($issetUser) == 0) {
                    $em->persist($user);
                    $em->flush();
                    $data["status"] = "success";
                    $data["code"] = 200;
                    $data["msg"] = "new user created";
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "User not created duplicated",
                );
            }
        }

        return $helpers->json($data);
    }

    public function editAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck == true) {

            $identity = $helpers->authCheck($hash, true);
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("BackendBundle:User")->findOneBy(
                array(
                    "id" => $identity->sub
                )
            );

            $json = $request->get("json", null);
            $params = json_decode($json);
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "User not updated",
            );
            if ($json != null) {
                $createdAt = new \DateTime("now");
                $image = null;
                $rol = "user";

                $email = (isset($params->email)) ? $params->email : null;
                $name = (isset($params->name)) ? $params->name : null;
                $surname = (isset($params->surname)) ? $params->surname : null;
                $password = (isset($params->password)) ? $params->password : null;

                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "This email not valid!!";
                $validateEmail = $this->get("validator")->validate($email, $emailConstraint);

                if ($email != null && count($validateEmail) == 0 && $name != null && $surname != null) {
                    $user->setCreateAt($createdAt);
                    $user->setImage($image);
                    $user->setRole($rol);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    if ($password != null) {
                        $pwd = hash("sha256", $password);
                        $user->setPassword($pwd);
                    }

                    $em = $this->getDoctrine()->getManager();
                    $issetUser = $em->getRepository("BackendBundle:User")->findBy(
                        array(
                            "email" => $email
                        )
                    );
                    if (count($issetUser) == 0 || $identity->email == $email) {
                        $em->persist($user);
                        $em->flush();
                        $data["status"] = "success";
                        $data["code"] = 200;
                        $data["msg"] = "User updated";
                    }
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "User not updated",
                    );
                }
            }
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Auth not valid",
            );
        }
        return $helpers->json($data);
    }

    public function uploadImageAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck) {
            $identity = $helpers->authCheck($hash, true);

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("BackendBundle:User")->findOneBy(
                array(
                    "id" => $identity->sub
                )
            );

            $file = $request->files->get("image");

            if (!empty($file) && $file != null) {
                $ext = $file->guessExtension();
                if ($ext == "jpeg" || $ext == "jpg" || $ext == "png" || $ext == "gif") {
                    $fileName = time() . "." . $ext;
                    $file->move("uploads/users", $fileName);

                    $user->setImage($fileName);
                    $em->persist($user);
                    $em->flush();
                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Image uploaded!",
                    );
                } else {
                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "file NOT VALID",
                    );
                }

            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Image NOT uploaded!",
                );
            }
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Auth not valid",
            );
        }
        return $helpers->json($data);
    }
}
