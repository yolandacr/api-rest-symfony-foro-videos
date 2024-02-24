<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Video;
use App\Services\JwtAuth;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

class VideoController extends AbstractController
{
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VideoController.php',
        ]);
    }

    public function create(Request $request, JwtAuth $jwt_auth, EntityManagerInterface $em){

        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El video no ha podido crearse'
        ];

        // Recoger el token
        $token = $request->headers->get('Authorization', null);

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);
        
        if($authCheck){
            // Recoger datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);

            // Recoger el objeto del usuario identificado
            $identity = $jwt_auth->checkToken($token, true);

        
            // Comprobar y validar datos
            if(!empty($json)){
                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (!empty($params->title)) ? $params->title : null;
                $description = (!empty($params->description)) ? $params->description : null;
                $url = (!empty($params->url)) ? $params->url : null;

                if(!empty($user_id) && !empty($title)){
                     // Guardar el nuevo video favorito en bd
                     $user_repo = $em->getRepository(User::class);

                     $user = $user_repo->findOneBy(array(
                        'id'=>$user_id
                    ));

                    // Crear y guardar objeto
                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setUrl($url);
                    $video->setStatus('normal');

                    $date = new \DateTime('now');
                    $video->setCreatedAt($date);
                    $video->setUpdatedAt($date);

                    //Guardar en bd
                    $em->persist($video);
                    $em->flush();


                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El video se ha guardado',
                        'video' => $video
                    ];

                }
            }

        }

        // Devolver una respuesta
        return $this->json($data);
    }

    public function videos(Request $request, JwtAuth $jwt_auth){

        // Recoger la cabecer ade autenticación

        // Comprobar el token

        // Si es válido,

        // Conseguir la identidad del usuario

        // Configurar el bundle de paginación

        // Hacer  una consulta para paginar

        // Recoger el parámetro page de la url

        // Invocar paginación

        // Preparar array de datos para devolver

        // Si falla, devolver esto.

        $data = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'No se pueden listar los videos en este momento',
        );

        return $this->json($data);
    }
}
