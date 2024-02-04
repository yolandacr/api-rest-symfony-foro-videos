<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Video;
use App\Services\JwtAuth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

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
        return $this->json($data);
    }

    public function create(Request $request, EntityManagerInterface $em){
        // Recoger los datos por POST
        $json = $request->get('json', null);

        // Decodificar el JSON
        $params = json_decode($json);

        // Hacer una respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 200,
            'message' => 'El usuario no se ha creado.'
        ];

        // Comprobar y validar datos
        if($json != null){
            $name = (!empty($params->name)) ? $params->name : null;
            $surname = (!empty($params->surname)) ? $params->surname : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);
            
            if(!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($name) && !empty($surname)){
                // Si la validación es correcta, crear objeto del usuario
                $user = new User();
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setRole('ROLE_USEER');
                $user->setCreatedAt(new \DateTime('now'));

                // Cifrar la contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($password);

                // Comprobar si el usuario existe
                $user_repo = $em->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email'=>$email
                ));

                // Si no existe guardamos en la base de datos
                if(count($isset_user) == 0){
                    //guardo el usuario
                    $em->persist($user);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario creado correctamente.',
                        'user' => $user
                    ];
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'El usuario ya existe.'
                    ];

                }
            }
        }

        // Hacer respuesta en Json
        return $this->json($data);
    }

    public function login(Request $request, JwtAuth $jwt_auth){
        //Recibir los datos por post
        $json = $request -> get('json', null);
        $params = json_decode($json);

        // Array por defecto para devolver
        $data = [
            'status' => 'error',
            'code' => 200,
            'message' => 'El usuario  no se ha podido identificar'
        ];

        // Comprobar y validar datos
        if($json != null){
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            $gettoken = (!empty($params->gettoken)) ? $params->gettoken : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]) ;
        }

        if(!empty($email) && !empty($password) && count($validate_email) == 0){
            // Cifrar la contraseña
            $pwd = hash('sha256', $password);

            // Si todo es válido llamaremos a un servicio para identificar al usuario (jwt, token o un objeto)
            //Crear servicio de jwt
           

            $data = [
                'message' =>  $jwt_auth->singup()
            ];

        }else{
            $data = [
                'message' => 'Validación incorrecta'
            ];

        }

        // Si nos devuelve bien los datos, respuesta
        return $this->json($data);



    }
}
