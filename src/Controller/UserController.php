<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Video;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $user_repo = $entityManager->getRepository(User::class);
        $video_repo = $entityManager->getRepository(Video::class);

        $users = $user_repo->findAll();
        $user = $user_repo->find(1);
        $videos = $video_repo->findAll();

        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];

        // foreach($users as $user){
        //     echo"<h1>{$user->getName()} {$user->getSurname()}</h1>";
        //     foreach ($user->getVideos() as $video) {
        //         echo"<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
        //     }
        // }

        // var_dump($user);

        // die();
        return $this->json($videos);
    }
}
