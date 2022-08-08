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
    public function store(Request $request, 
                            ValidatorInterface $validator,
                            TopicRepository $topicRepository, 
                            PostRepository $postRepository,
                            BbCode $bbCode)
    {
        $content = $request->request->get('content');
        $topic_id = $request->request->get('topic_id');
        $topic = $topicRepository->findOneBy(['id'=>$topic_id]);
        $user = $this->getUser();
    
        $htmlContent = $bbCode->codeToHtml($content);

        $post = new Post();
        $post->setUser($user);
        $post->setTopic($topic);
        $post->setContent($htmlContent);

        $errors = $validator->validate($post);
        if (count($errors)>0) {
            return new Response('todo post.store error');
        }
    
        $postRepository->add($post, true);
        $now = new DateTime();
        $topic->setUpdatedAt($now);
        $topicRepository->add($topic, true);

        return $this->redirect('/topic/'.$topic_id);
    }

    #[Route('/post.delete', name: 'app_post_delete')]
    public function delete(Request $request, PostRepository $postRepository)
    {
        $post_id = $request->get('post_id');
        $post = $postRepository->findOneBy(['id'=>$post_id]);
        $topic_id = $post->getTopic()->getId();
        
        if($post->getUser()->getUserIdentifier() != $this->getUser()->getUserIdentifier()) {
            return new Response('Operação não permitida');
        }

        $postRepository->remove($post, true);

        return $this->render('/topic/'.$topic_id);

    }
    

    
}
