<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Entity\Forum;
use App\Repository\ForumRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use DateTime;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(CategoryRepository $categoryRepository, ForumRepository $forumRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $forums = $forumRepository->findAll();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'categories' => $categories,
            'forums' => $forums,
        ]);
    }

    #[Route('/admin/users', methods:['GET'], name: 'app_admin_users')]
    public function users(Request $request, UserRepository $userRepository): Response
    {
        $limit = 20;
        $total = $userRepository->countAll();
        $maxPages = max(1, (int) ceil($total / $limit));
        $page = min(max(1, (int) $request->query->get('page', 1)), $maxPages);
        $users = $userRepository->findPage($page, $limit);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'page' => $page,
            'maxPages' => $maxPages,
            'total' => $total,
        ]);
    }

    #[Route('/admin/users.toggle', methods:['POST'], name: 'app_admin_users_toggle')]
    public function toggleUser(Request $request, UserRepository $userRepository): Response
    {
        if (!$this->isCsrfTokenValid('admin.user.toggle', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $user_id = (int) $request->request->get('user_id');
        $page = max(1, (int) $request->request->get('page', 1));

        $target = $userRepository->find($user_id);
        if (!$target) {
            throw $this->createNotFoundException('Usuário não encontrado');
        }

        if ($this->getUser() && $target->getId() === $this->getUser()->getId()) {
            return new Response('Você não pode desativar sua própria conta', Response::HTTP_FORBIDDEN);
        }

        $target->setEnabled(!$target->isEnabled());
        $userRepository->add($target, true);

        return $this->redirectToRoute('app_admin_users', ['page' => $page]);
    }

    #[Route('/admin/create.category', name: 'app_admin_create_category')]
    public function createCategory(Request $request, CategoryRepository $categoryRepository)
    {
        if (!$this->isCsrfTokenValid('admin.create.category', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $title = $request->request->get('title');
        $position = $request->request->get('position');
        $category = new Category();
        $category->setTitle($title);
        $category->setPosition($position);
        $categoryRepository->add($category, true);

        return $this->redirect('/admin');
    }

    #[Route('/admin/create.forum', name: 'app_admin_create_forum')]
    public function createForum(Request $request, CategoryRepository $categoryRepository, ForumRepository $forumRepository)
    {
        if (!$this->isCsrfTokenValid('admin.create.forum', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $category_id = $request->request->get('category_id');
        $position = $request->request->get('position');
        $category = $categoryRepository->findOneBy(['id' => $category_id]);
        $forum = new Forum();
        $forum->setTitle($title);
        $forum->setDescription($description);
        $forum->setPosition($position);
        $forum->setCategory($category);
        $forumRepository->add($forum, true);

        return $this->redirect('/admin');
    }

    #[Route('/admin/delete.post', name: 'app_admin_delete_post')]
    public function deletePost(Request $request, PostRepository $postRepository)
    {
        if (!$this->isCsrfTokenValid('admin.delete.post', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $post_id = $request->request->get('post_id');
        $post = $postRepository->findOneBy(['id' => $post_id]);

        if (!$post) {
            throw $this->createNotFoundException('Post não encontrado');
        }

        $now = new DateTime();
        $post->setDeletedAt($now);
        $post->setContent('<b class="deleted-post"> Mensagem Apagada pelo moderador </b>');

        $postRepository->add($post, true);

        return $this->redirect('/topic/' . $post->getTopic()->getId());
    }

    #[Route('/admin/update.category', name: 'app_admin_update_category')]
    public function updateCategory(Request $request, CategoryRepository $categoryRepository)
    {
        if (!$this->isCsrfTokenValid('admin.update.category', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $category_id = $request->request->get('category_id');
        $title = $request->request->get('title');
        $position = $request->request->get('position');

        $category = $categoryRepository->findOneBy(['id' => $category_id]);
        if (!$category) {
            throw $this->createNotFoundException('Categoria não encontrada');
        }

        $category->setTitle($title);
        $category->setPosition($position);
        $categoryRepository->add($category, true);

        return $this->redirect('/admin');
    }

    #[Route('/admin/update.forum', name: 'app_admin_update_forum')]
    public function updatePost(Request $request, ForumRepository $forumRepository, CategoryRepository $categoryRepository)
    {
        if (!$this->isCsrfTokenValid('admin.update.forum', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $forum_id = $request->request->get('forum_id');
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $positon = $request->request->get('position');
        $forum = $forumRepository->findOneBy(['id' => $forum_id]);
        $category_id = $request->request->get('category_id');
        $category = $categoryRepository->findOneBy(['id' => $category_id]);

        $forum->setTitle($title);
        $forum->setDescription($description);
        $forum->setPosition($positon);
        $forum->setCategory($category);

        $forumRepository->add($forum, true);

        return $this->redirect('/admin');
    }
}