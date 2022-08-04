<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    #[Route('/user',methods:['GET'], name: 'app_user')]
    public function index()
    {
        $user = $this->getUser();
        $avatar_url = $this->getParameter('app.avatar_bucket_url');
        $avatars = json_decode(file_get_contents($avatar_url));
        return $this->render('/user/index.html.twig',['user'=>$user,'url'=>$avatar_url,'avatars'=>$avatars->objects]);
    }
    
    #[Route('/user/{id}',methods:['GET'], name: 'app_user_profile')]
    public function show(int $id, UserRepository $userRepository)
    {
        $user = $userRepository->findOneById($id);
        $avatar_url = $this->getParameter('app.avatar_bucket_url');
        $avatars = json_decode(file_get_contents($avatar_url));
        if(!$user) {
            return new Response('<h1> OPS! </h1');
        }
        return $this->render('/user/index.html.twig',['user'=>$user,'url'=>$avatar_url,'avatars'=>$avatars->objects]);
    }

    #[Route('/login', methods:['GET','POST'], name: 'app_user_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $contents = $request->getContent();
        if($contents) {
           if(!$this->isCsrfTokenValid('login', $request->request->get('token'))) {
                return $this->render('/user/login.html.twig',['message'=>'error csrf']);
            }

        }
        return $this->render('/user/login.html.twig',['error' => $authenticationUtils->getLastAuthenticationError()]);
    }

    #[Route('/logout', name: 'app_user_logout')]
    public function logout()
    {
        
    }   
    
    #[Route('/user.create', methods:['GET'], name: 'app_user_create')]
    public function create()
    {
        $avatar_url = $this->getParameter('app.avatar_bucket_url');
        $avatars = json_decode(file_get_contents($avatar_url));
        //dd($avatars);
        return $this->render('/user/create.html.twig',['url'=>$avatar_url,'avatars'=>$avatars->objects]);
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
        $avatar = $content->get('avatar');
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPlainPassword($plainPassword);
        $user->setAvatar($avatar);
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

    
    #[Route('/user.update',methods:['POST'], name: 'app_user_update')]
    public function update(Request $request, UserPasswordHasherInterface $hasher, UserRepository $userRepository)
    {
        $content = $request->request;
        $email = $content->get('email');
        $plainPassword = $content->get('password');
        $avatar = $content->get('avatar');

        $user = $this->getUser();
        
        $user = $userRepository->findOneBy(['email'=>$user->getUserIdentifier()]); 
        $user->setEmail($email);
        $user->setAvatar($avatar);

        if($plainPassword) {
            $hashedPassword = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        $userRepository->add($user, true);

        return $this->redirect('/user');
        
    }
}
