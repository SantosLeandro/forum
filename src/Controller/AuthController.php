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
        if ($request->isMethod('POST') && !$this->isCsrfTokenValid('login', $request->request->get('token'))) {
            return $this->render('/auth/index.html.twig', ['message' => 'Sessão expirada. Tente novamente.']);
        }
        return $this->render('/auth/index.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'app_user_logout')]
    public function logout()
    {
        throw new \LogicException('Este método é interceptado pelo firewall.');
    }
}
