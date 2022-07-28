<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;

class TopicController extends AbstractController
{
    #[Route('topic/{id}', name: 'app_topic')]
    public function index(int $id, PostRepository $postRepository)
    {
        $posts = $postRepository->findBy(['topic'=>['id'=>$id]]);
        return $this->render('topic/index.html.twig',['posts'=>$posts]); 
        
    }
}
