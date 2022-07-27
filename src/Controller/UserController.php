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

    #[Route('/login', methods:['GET','POST'], name: 'app_user_login')]
    public function login(Request $request)
    {
        $contents = $request->getContent();
        if($contents) {
           if(!$this->isCsrfTokenValid('login', $request->request->get('token'))) {
                return $this->render('/user/login.html.twig',['message'=>'error csrf']);
            }

        }
        return $this->render('/user/login.html.twig');
    }

    #[Route('/logout', name: 'app_user_logout')]
    public function logout()
    {
        
    }   
    
    #[Route('user.create', methods:['GET'], name: 'app_user_create')]
    public function create()
    {
        return $this->render('/user/create.html.twig');
    }

    #[Route('/user.store',methods:['POST'], name: 'app_user_store')]
    public function store(Request $request, 
                            ValidatorInterface $validator, 
                            UserPasswordHasherInterface $hasher, 
                            ManagerRegistry $doctrine)
    {

        if(!$this->isCsrfTokenValid('user.store', $request->request->get('token'))) {
            return $this->render('/user/login.html.twig',['message'=>'error']);
        }

        $content = $request->request;
        $username = $content->get('username');
        $email = $content->get('email');
        $plainPassword = $content->get('password');
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
        
        return $this->render('user/login.html.twig',['message'=>'usuário criado']);
        
    }
}
