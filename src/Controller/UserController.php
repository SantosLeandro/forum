<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;

class UserController extends AbstractController
{
    #[Route('/user',methods:['GET'], name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/user.create',methods:['POST'], name: 'app_user_create')]
    public function create(Request $request, 
                            ValidatorInterface $validator, 
                            UserPasswordHasherInterface $hasher, 
                            ManagerRegistry $doctrine): JsonResponse
    {
        $content = json_decode($request->getContent());
        $username = $content->username;
        $email = $content->email;
        $plainPassword = $content->password;
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPlainPassword($plainPassword);
        $errors = $validator->validate($user);
        if(count($errors) > 0) {
            $errorsString = (string)$errors;
            return $this->json(['message'=>$errorsString]);
        }
        $user->eraseCredentials();

        $hashedPassword = $hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        
        return $this->json(['message'=>'create user']);
    }
}
