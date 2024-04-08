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
use Knp\Component\Pager\PaginatorInterface;

class VideoController extends AbstractController
{
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VideoController.php',
        ]);
    }

    public function create(Request $request, JwtAuth $jwt_auth, EntityManagerInterface $em, $id = null){

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

                   

                    if($id == null){
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

                    }else{
                        $video = $em->getRepository(Video::class)->findOneBy([
                            'id'=>$id,
                            'user'=>$identity->sub
                        ]);


                        if($video && is_object($video)){
                            $video->setTitle($title);
                            $video->setDescription($description);
                            $video->setUrl($url);
                          
                            $date = new \DateTime('now');
                            $video->setUpdatedAt($date);

                            $em->persist($video);
                            $em->flush();

                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'El video se ha actualizado',
                                'video' => $video
                            ];

                        }
                        
                    }

                 

                }
            }

        }

        // Devolver una respuesta
        return $this->json($data);
    }

    public function videos(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator, EntityManagerInterface $em){

        // Recoger la cabecer ade autenticación
        $token = $request->headers->get('Authorization');
      
        // Comprobar el token
        $authCheck = $jwt_auth->checkToken($token);

         // Si es válido,
        if($authCheck){

            // Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token,true);

            // Hacer  una consulta para paginar
            $dql = "SELECT v FROM App\Entity\Video v WHERE v.user = {$identity->sub} ORDER BY v.id DESC";
            $query = $em->createQuery($dql);


            // Recoger el parámetro page de la url
            $page = $request->query->getInt('page', 1);
            $items_per_page = 5;

            // Invocar paginación
            $pagination = $paginator->paginate($query,$page,$items_per_page);
            $total = $pagination->getTotalItemCount();

            // Preparar array de datos para devolver
            $data = array(
                'status' => 'success',
                'code' => 200,
                'total_items_count' => $total,
                'page_actual' => $page,
                'items_per_page' => $items_per_page,
                'total_pages' => ceil($total / $items_per_page),
                'videos' => $pagination,
                'user_id' => $identity->sub
            );


        }else{
            // Si falla, devolver esto.

            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pueden listar los videos en este momento',
            );
        }

        return $this->json($data);
    }

    public function video(Request $request, JwtAuth $jwt_auth, $id, EntityManagerInterface $entityManager){

        // Sacar el token y comprobar si es correcto
        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        // Devolver una respuesta
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'Video no encontrado'
        ];

        if($authCheck){
        // Sacar la identidad del usuario
        $identity = $jwt_auth->checkToken($token,true);

        // Sacar el objeto del video en base al id
        $video = $entityManager->getRepository(Video::class)->findOneBy([
            'id' => $id
        ]);

        // Comprobar si el video existe y es propiedad del usuario identificado
        if($video && is_object($video) && $identity->sub == $video->getUser()->getId()){
            $data = [
                'status' => 'success',
                'code' => 200,
                'video' => $video
            ];
        }

        }
         
        return $this->json($data);
    }

    public function remove ( Request $request, JwtAuth $jwt_auth, $id, EntityManagerInterface $entityManager){
        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        

        // Devolver una respuesta
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'Video no encontrado'
        ];

        if($authCheck){
            $identity = $jwt_auth->checkToken($token,true);
            

            $video = $entityManager->getRepository(Video::class)->findOneBy([
                'id' => $id
            ]);

            if($video && is_object($video) && $identity->sub == $video->getUser()->getId()){
                $entityManager->remove($video);
                $entityManager->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'video' => $video
                ];
            }

        }

        return $this->json($data);

    }
}
