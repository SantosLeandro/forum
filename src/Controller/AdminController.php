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

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'categories'=>$categories
        ]);
    }
    
    #[Route('/admin/create.category', name: 'app_admin_create_category')]
    public function createCategory(Request $request, CategoryRepository $categoryRepository)
    {
        $title = $request->get('title');
        $position = $request->get('position');
        $category = new Category();
        $category->setTitle($title);
        $category->setPosition($position);
        $categoryRepository->add($category, true);
        
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/create.forum', name: 'app_admin_create_forum')]
    public function createForum(Request $request, CategoryRepository $categoryRepository, ForumRepository $forumRepository)
    {
        $title = $request->get('title');
        $description = $request->get('description');
        $category_id = $request->get('category_id');
        $position = $request->get('position');
        $category = $categoryRepository->findOneBy(['id'=>$category_id]);
        $forum = new Forum();
        $forum->setTitle($title);
        $forum->setDescription($description);
        $forum->setPosition($position);
        $forum->setCategory($category);
        $forumRepository->add($forum, true); 
 
        return $this->render('admin/index.html.twig');
    }

    
}
