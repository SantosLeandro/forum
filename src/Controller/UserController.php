<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;

class UserController extends AbstractController
{
    #[Route('/users',methods:['GET'], name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/users.create',methods:['POST'], name: 'app_user_create')]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $content = json_decode($request->getContent());
        $username = $content->username;
        $email = $content->email;
        $password = $content->password;
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        
        $errors = $validator->validate($user);
        if(count($errors) > 0) {
            $errorsString = (string)$errors;
            return $this->json(['message'=>$errorsString]);
        }
        
        return $this->json(['message'=>'create user']);
    }
}
