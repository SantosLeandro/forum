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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    private function getAvatars(): array
    {
        $finder = new Finder();
        $finder->files()->in($this->getParameter('kernel.project_dir') . '/public/assets/avatars');

        $avatars = [];
        foreach ($finder as $file) {
            $avatars[] = [
                'name' => $file->getFilename(),
                'url' => $this->getParameter('app.avatar_bucket_url') . $file->getFilename(),
            ];
        }
        return $avatars;
    }

    private function getAllowedAvatarNames(): array
    {
        return array_map(static fn (array $avatar): string => $avatar['name'], $this->getAvatars());
    }

    #[Route('/users', methods:['GET'], name: 'app_user')]
    #[IsGranted('ROLE_USER')]
    public function index()
    {
        $user = $this->getUser();
        return $this->render('/user/index.html.twig', [
            'user' => $user,
            'url' => $this->getParameter('app.avatar_bucket_url'),
            'avatars' => $this->getAvatars(),
        ]);
    }

    #[Route('/users/{id}', methods:['GET'], name: 'app_user_profile')]
    public function show(int $id, UserRepository $userRepository)
    {
        $user = $userRepository->findOneById($id);
        if (!$user) {
            return new Response('<h1> OPS! </h1>');
        }
        return $this->render('/user/index.html.twig', [
            'user' => $user,
            'url' => $this->getParameter('app.avatar_bucket_url'),
            'avatars' => $this->getAvatars(),
        ]);
    }

    #[Route('/user.create', methods:['GET'], name: 'app_user_create')]
    public function create()
    {
        return $this->render('/user/create.html.twig', [
            'url' => $this->getParameter('app.avatar_bucket_url'),
            'avatars' => $this->getAvatars(),
        ]);
    }

    #[Route('/users', methods:['POST'], name: 'app_user_store')]
    public function store(Request $request,
                            ValidatorInterface $validator,
                            UserPasswordHasherInterface $hasher,
                            ManagerRegistry $doctrine)
    {
        if (!$this->isCsrfTokenValid('user.store', $request->request->get('token'))) {
            return $this->renderCreate('Sessão expirada. Tente novamente.');
        }

        $content = $request->request;
        $username = trim((string) $content->get('username'));
        $email = trim((string) $content->get('email'));
        $plainPassword = (string) $content->get('password');
        $passwordConfirmation = (string) $content->get('password_confirmation');
        $avatar = $content->get('avatar');

        if ($plainPassword !== $passwordConfirmation) {
            return $this->renderCreate('As senhas não coincidem', [
                'username' => $username,
                'email' => $email,
                'selected_avatar' => $avatar,
            ]);
        }

        $allowedAvatars = $this->getAllowedAvatarNames();
        if (null === $avatar) {
            return $this->renderCreate('Selecione um avatar', [
                'username' => $username,
                'email' => $email,
            ]);
        }
        if (!in_array($avatar, $allowedAvatars, true)) {
            return $this->renderCreate('Avatar inválido', [
                'username' => $username,
                'email' => $email,
                'selected_avatar' => $avatar,
            ]);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPlainPassword($plainPassword);
        $user->setAvatar($avatar);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->renderCreate(array_map(
                fn ($error) => $error->getMessage(),
                iterator_to_array($errors)
            ), [
                'username' => $username,
                'email' => $email,
                'selected_avatar' => $avatar,
            ]);
        }

        $user->eraseCredentials();

        $hashedPassword = $hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->renderCreate('Este email ou nome de usuário já está em uso', [
                'username' => $username,
                'email' => $email,
                'selected_avatar' => $avatar,
            ]);
        }

        return $this->redirectToRoute('app_auth_login');
    }

    /**
     * @param string|string[] $error
     */
    private function renderCreate(string|array $error, array $extra = []): Response
    {
        $data = [
            'url' => $this->getParameter('app.avatar_bucket_url'),
            'avatars' => $this->getAvatars(),
        ];

        if (is_array($error)) {
            $data['messages'] = $error;
        } else {
            $data['message'] = $error;
        }

        return $this->render('/user/create.html.twig', array_merge($data, $extra));
    }

    #[Route('/users/update', methods:['POST'], name: 'app_user_update')]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, UserPasswordHasherInterface $hasher, ValidatorInterface $validator, UserRepository $userRepository)
    {
        if (!$this->isCsrfTokenValid('user.update', $request->request->get('token'))) {
            return $this->renderProfile('error csrf');
        }

        $content = $request->request;
        $email = trim((string) $content->get('email'));
        $plainPassword = (string) $content->get('password');
        $passwordConfirmation = (string) $content->get('password_confirmation');
        $avatar = $content->get('avatar');

        $user = $this->getUser();

        if ($plainPassword !== '' && $plainPassword !== $passwordConfirmation) {
            return $this->renderProfile('As senhas não coincidem');
        }

        $allowedAvatars = $this->getAllowedAvatarNames();
        if (null === $avatar || !in_array($avatar, $allowedAvatars, true)) {
            return $this->renderProfile('Selecione um avatar válido');
        }

        $user->setEmail($email);
        $user->setAvatar($avatar);

        if ($plainPassword) {
            $user->setPlainPassword($plainPassword);
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->renderProfile($errors->get(0)->getMessage());
        }

        if ($plainPassword) {
            $user->setPassword($hasher->hashPassword($user, $plainPassword));
        }

        try {
            $userRepository->add($user, true);
        } catch (UniqueConstraintViolationException) {
            return $this->renderProfile('Este email ou nome de usuário já está em uso');
        }

        return $this->redirectToRoute('app_user');
    }

    private function renderProfile(string $message): Response
    {
        return $this->render('/user/index.html.twig', [
            'user' => $this->getUser(),
            'url' => $this->getParameter('app.avatar_bucket_url'),
            'avatars' => $this->getAvatars(),
            'message' => $message,
        ]);
    }
}