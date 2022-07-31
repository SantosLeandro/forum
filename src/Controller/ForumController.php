<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TopicRepository;
use App\Repository\PostRepository;
class ForumController extends AbstractController
{
    #[Route('/forum/{id}', name: 'app_forum')]
    public function index(int $id, TopicRepository $topicRepository)
    {
        $topics = $topicRepository->findBy(['forum'=>['id'=>$id]],['updatedAt'=>'DESC']); 
        return $this->render('forum/index.html.twig', ['topics'=>$topics, 'forum_id'=>$id]);
    }

    

}
