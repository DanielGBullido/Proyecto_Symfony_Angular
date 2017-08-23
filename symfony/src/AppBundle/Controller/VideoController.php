<?php
/**
 * Created by PhpStorm.
 * User: danielgbullido
 * Date: 15/8/17
 * Time: 18:36
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;

class VideoController extends Controller
{
    public function newAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck == true) {

            $identity = $helpers->authCheck($hash, true);
            $json = $request->get("json", null);
            $params = json_decode($json);

            if ($json != null) {
                $createAt = new \DateTime("now");
                $updateAt = new \DateTime("now");
                $image = null;
                $videoPath = null;

                $userId = ($identity->sub != null) ? $identity->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if ($userId != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();
                    $user = $em->getRepository("BackendBundle:User")->findOneBy(
                        array(
                            "id" => $userId
                        )
                    );

                    $video = new Video();
                    $video->setCreateAt($createAt);
                    $video->setUpdateAt($updateAt);
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setStatus($status);

                    $em->persist($video);
                    $em->flush();

                    $video = $em->getRepository("BackendBundle:Video")->findOneBy(
                        array(
                            "user" => $user,
                            "title" => $title,
                            "status" => $status
                        )
                    );

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "data" => $video,
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Video NOT created",
                    );
                }

            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Video NOT created",
                );
            }
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Auth NOT valid",
            );
        }
        return $helpers->json($data);
    }

    public function editAction(Request $request, $videoId = null)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck == true) {

            $identity = $helpers->authCheck($hash, true);
            $json = $request->get("json", null);
            $params = json_decode($json);

            if ($json != null) {
                $updateAt = new \DateTime("now");
                $image = null;
                $videoPath = null;

                $userId = ($identity->sub != null) ? $identity->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if ($userId != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();

                    $video = $em->getRepository("BackendBundle:Video")->findOneBy(
                        array(
                            "id" => $videoId,
                        )
                    );

                    if (isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
                        $video->setUpdateAt($updateAt);
                        $video->setTitle($title);
                        $video->setDescription($description);
                        $video->setStatus($status);

                        $em->persist($video);
                        $em->flush();

                        $data = array(
                            "status" => "success",
                            "code" => 200,
                            "msg" => "VIDEO UPDATED!!",
                        );
                    } else {
                        $data = array(
                            "status" => "success",
                            "code" => 400,
                            "msg" => "VIDEO NOT UPDATED YOU NOT OWNER!!",
                        );
                    }
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Video NOT created",
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Video NOT created",
                );
            }
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Auth NOT valid",
            );
        }
        return $helpers->json($data);
    }

    public function uploadAction(Request $request, $videoId = null)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck == true) {

            $identity = $helpers->authCheck($hash, true);
            $em = $this->getDoctrine()->getManager();
            $video = $em->getRepository("BackendBundle:Video")->findOneBy(
                array(
                    "id" => $videoId
                )
            );

            if ($videoId != null && isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {

                $file = $request->files->get("image", null);
                $fileVideo = $request->files->get("video", null);

                if ($file != null && !empty($file)) {
                    $ext = $file->guessExtension();
                    $fileName = time() . "." . $ext;
                    $pathOfFile = "uploads/video_image/video_" . $videoId;
                    $file->move($pathOfFile, $fileName);
                    $video->setImage($fileName);
                    $em->persist($video);
                    $em->flush();

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Video image uploaded",
                    );
                } elseif ($fileVideo != null && !empty($fileVideo)) {
                    $ext = $fileVideo->guessExtension();
                    if ($ext == "mp4" || $ext == "avi") {
                        $fileName = time() . "." . $ext;
                        $pathOfFile = "uploads/video_files/video_" . $videoId;
                        $fileVideo->move($pathOfFile, $fileName);
                        $video->setVideoPath($fileName);

                        $em->persist($video);
                        $em->flush();

                        $data = array(
                            "status" => "success",
                            "code" => 200,
                            "msg" => "Video uploaded",
                        );
                    }
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Auth NOT valid",
                );
            }

        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Auth NOT valid",
            );
        }
        return $helpers->json($data);
    }

    public function videosAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT v FROM BackendBundle:Video v ORDER BY v.id DESC";
        $query = $em->createQuery($dql);

        $page = $request->query->getInt("page", 1);
        $paginator = $this->get("knp_paginator");
        $itemsPerPage = 6;

        $pagination = $paginator->paginate($query, $page, $itemsPerPage);
        $totalItemsCount = $pagination->getTotalItemCount();

        $data = array(
            "status" => "success",
            "total_items_count" => $totalItemsCount,
            "page_actual" => $page,
            "items_per_page" => $itemsPerPage,
            "total_pages" => ceil($totalItemsCount / $itemsPerPage),
            "data" => $pagination
        );

        return $helpers->json($data);
    }

    public function lastsVideosAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT v FROM BackendBundle:Video v ORDER BY v.createAt DESC";
        $query = $em->createQuery($dql)->setMaxResults(5);
        $videos = $query->getResult();

        $data = array(
            "status" => "success",
            "data" => $videos
        );

        return $helpers->json($data);
    }

    public function videoAction(Request $request, $id = null)
    {
        $helpers = $this->get("app.helpers");

        $em = $this->getDoctrine()->getManager();

        $video = $em->getRepository("BackendBundle:Video")->findOneBy(
            array(
                "id" => $id
            )
        );

        if ($video) {
            $data = array(
                "status" => "success",
                "code" => 200,
                "data" => $video
            );
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "video not exists"
            );
        }

        return $helpers->json($data);
    }

    public function searchAction(Request $request, $search = null)
    {
        $helpers = $this->get("app.helpers");

        $em = $this->getDoctrine()->getManager();

        if ($search != null) {
            $dql = "SELECT v FROM BackendBundle:Video v WHERE" .
                "v.title like '%$search%' OR " .
                "v.description like '%$search%' OR " .
                "ORDER BY v.id DESC";
        } else {
            $dql = "SELECT v FROM BackendBundle:Video v ORDER BY v.id DESC";
        }


        $dql = "SELECT v FROM BackendBundle:Video v ORDER BY v.id DESC";
        $query = $em->createQuery($dql);

        $page = $request->query->getInt("page", 1);
        $paginator = $this->get("knp_paginator");
        $itemsPerPage = 6;

        $pagination = $paginator->paginate($query, $page, $itemsPerPage);
        $totalItemsCount = $pagination->getTotalItemCount();

        $data = array(
            "status" => "success",
            "total_items_count" => $totalItemsCount,
            "page_actual" => $page,
            "items_per_page" => $itemsPerPage,
            "total_pages" => ceil($totalItemsCount / $itemsPerPage),
            "data" => $pagination
        );

        return $helpers->json($data);
    }
}