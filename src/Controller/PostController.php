<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PostRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\BbCode;
use App\Entity\Post;
use DateTime;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(TopicRepository $topicRepository): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PostController.php',
        ]);
    }

    #[Route('/post.store', name: 'app_post_store')]
    #[IsGranted('ROLE_USER')]
    public function store(Request $request,
                            ValidatorInterface $validator,
                            TopicRepository $topicRepository,
                            PostRepository $postRepository,
                            BbCode $bbCode)
    {
        if (!$this->isCsrfTokenValid('post.store', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $content = $request->request->get('content');
        $topic_id = $request->request->get('topic_id');
        $topic = $topicRepository->findOneBy(['id' => $topic_id]);

        if (!$topic) {
            return new Response('Tópico inválido', Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();

        $htmlContent = $bbCode->codeToHtml((string) $content);

        $post = new Post();
        $post->setUser($user);
        $post->setTopic($topic);
        $post->setContent($htmlContent);

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return new Response('Dados do post inválidos', Response::HTTP_BAD_REQUEST);
        }

        $postRepository->add($post, true);
        $now = new DateTime();
        $topic->setUpdatedAt($now);
        $topicRepository->add($topic, true);

        return $this->redirect('/topic/' . $topic_id);
    }

    #[Route('/post/{id}', methods:['GET'], name: 'app_post_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(int $id, PostRepository $postRepository, BbCode $bbCode)
    {
        $post = $postRepository->findOneBy(['id' => $id]);
        if (!$post) {
            throw $this->createNotFoundException('Post não encontrado');
        }
        if ($post->getUser()->getId() !== $this->getUser()->getId()) {
            return new Response('OPS!');
        }
        return $this->render('post/index.html.twig', [
            'post' => $post,
            'bbcode' => $bbCode->htmlToCode((string) $post->getContent()),
        ]);
    }

    #[Route('/post.update', methods:['POST'], name: 'app_post_update')]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, PostRepository $postRepository, BbCode $bbCode, ValidatorInterface $validator)
    {
        if (!$this->isCsrfTokenValid('post.update', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $post_id = $request->request->get('post_id');
        $post = $postRepository->findOneBy(['id' => $post_id]);

        if (!$post) {
            throw $this->createNotFoundException('Post não encontrado');
        }

        if ($post->getUser()->getId() !== $this->getUser()->getId()) {
            return new Response('Operação não permitida', Response::HTTP_FORBIDDEN);
        }

        $content = $request->request->get('content');
        $post->setContent($bbCode->codeToHtml((string) $content));

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return new Response('Dados do post inválidos', Response::HTTP_BAD_REQUEST);
        }

        $postRepository->add($post, true);

        return $this->redirect('/topic/' . $post->getTopic()->getId());
    }

    #[Route('/post.delete', methods:['POST'], name: 'app_post_delete')]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, PostRepository $postRepository)
    {
        if (!$this->isCsrfTokenValid('post.delete', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $post_id = $request->request->get('post_id');
        $post = $postRepository->findOneBy(['id' => $post_id]);

        if (!$post) {
            throw $this->createNotFoundException('Post não encontrado');
        }

        $topic_id = $post->getTopic()->getId();

        if ($post->getUser()->getId() !== $this->getUser()->getId()) {
            return new Response('Operação não permitida', Response::HTTP_FORBIDDEN);
        }

        $postRepository->remove($post, true);

        return $this->redirect('/topic/' . $topic_id);
    }
}