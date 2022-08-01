<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TopicRepository;
use App\Repository\ForumRepository;
class ForumController extends AbstractController
{
    #[Route('/forum/{id}', name: 'app_forum')]
    public function index(int $id, TopicRepository $topicRepository, ForumRepository $forumRepository)
    {
        $topics = $topicRepository->findBy(['forum'=>['id'=>$id]],['updatedAt'=>'DESC']); 
        $forum = $forumRepository->findOneBy(['id'=>$id]);
        return $this->render('forum/index.html.twig', ['topics'=>$topics, 'forum'=>$forum]);
    }

    

}
