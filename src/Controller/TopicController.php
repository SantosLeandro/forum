<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TopicRepository;

class TopicController extends AbstractController
{
    #[Route('topic/{id}', name: 'app_topic')]
    public function index(int $id, TopicRepository $topicRepository)
    {
        $topic = $topicRepository->findBy(['forum'=>['id'=>$id]]);
        dd($topic);
        
    }
}
