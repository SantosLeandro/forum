<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth')]
    public function index(): Response
    {
        return $this->render('auth/index.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/login', methods:['GET','POST'], name: 'app_auth_login')]
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
}
