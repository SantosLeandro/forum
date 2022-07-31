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

class TopicController extends AbstractController
{
    #[Route('topic/{id}', name: 'app_topic')]
    public function index(int $id, PostRepository $postRepository)
    {
        $posts = $postRepository->findBy(['topic'=>['id'=>$id]]);
        $topic = $posts[0]->getTopic();
        return $this->render('topic/index.html.twig',['posts'=>$posts,'topic'=>$topic]); 
        
    }

    #[Route('topic.store', name: 'app_topic_store')]
    public function store(Request $request, 
                            ValidatorInterface $validator,
                            TopicRepository $topicRepository, 
                            PostRepository $postRepository, 
                            ForumRepository $forumRepository,
                            BbCode $bbCode)
    {
        $title = $request->request->get('title');
        $content = $request->request->get('content');
        $forum_id = $request->request->get('forum_id');
        $forum = $forumRepository->findOneBy(['id'=>$forum_id]);

        $topic = new Topic();
        $user = $this->getUser();
        $topic->setUser($user);
        $topic->setTitle($title);
        $topic->setForum($forum);

        $errors = $validator->validate($topic);
        if( count($errors) ) {
            return new Response('todo error topic invalid');
        }

        $htmlContent = $bbCode->codeToHtml($content); 

        $post = new Post(); 
        $post->setUser($user);
        $post->setContent($htmlContent);
        $post->setTopic($topic);
        
        $errors = $validator->validate($post);

        if( count($errors) ) {
            return new Response('todo error topic invalid');
        }
    
        $topicRepository->add($topic, true);
        $postRepository->add($post, true);

        return $this->redirect('topic/'.$topic->getId());
        
        

    }
}
