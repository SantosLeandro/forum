<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
use App\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\BbCode;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TopicController extends AbstractController
{
    #[Route('topic/{id}', name: 'app_topic')]
    public function index(int $id, PostRepository $postRepository, TopicRepository $topicRepository)
    {
        $topic = $topicRepository->find($id);
        if (!$topic) {
            throw $this->createNotFoundException('Tópico não encontrado');
        }
        $posts = $postRepository->findBy(['topic' => $topic], ['id' => 'ASC']);
        $url = $this->getParameter('app.avatar_bucket_url');
        return $this->render('topic/index.html.twig', ['posts' => $posts, 'topic' => $topic, 'url' => $url]);
    }

    #[Route('topic.store', name: 'app_topic_store')]
    #[IsGranted('ROLE_USER')]
    public function store(Request $request,
                            ValidatorInterface $validator,
                            TopicRepository $topicRepository,
                            PostRepository $postRepository,
                            ForumRepository $forumRepository,
                            BbCode $bbCode)
    {
        if (!$this->isCsrfTokenValid('topic.store', $request->request->get('token'))) {
            return new Response('CSRF inválido', Response::HTTP_BAD_REQUEST);
        }

        $title = $request->request->get('title');
        $content = $request->request->get('content');
        $forum_id = $request->request->get('forum_id');
        $forum = $forumRepository->findOneBy(['id' => $forum_id]);

        if (!$forum) {
            return new Response('Fórum inválido', Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();

        $topic = new Topic();
        $topic->setUser($user);
        $topic->setTitle($title);
        $topic->setForum($forum);

        $errors = $validator->validate($topic);
        if (count($errors)) {
            return new Response('Dados do tópico inválidos', Response::HTTP_BAD_REQUEST);
        }

        $htmlContent = $bbCode->codeToHtml((string) $content);

        $post = new Post();
        $post->setUser($user);
        $post->setContent($htmlContent);
        $post->setTopic($topic);

        $errors = $validator->validate($post);

        if (count($errors)) {
            return new Response('Dados do post inválidos', Response::HTTP_BAD_REQUEST);
        }

        $topicRepository->add($topic, true);
        $postRepository->add($post, true);

        return $this->redirect('topic/' . $topic->getId());
    }
}