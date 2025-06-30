<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryForm;
use App\Repository\CategoryRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }


    //---------------------------------------------------------------------------

    #[Route('/category/create', name: 'app_category_create')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryForm::class , $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    //---------------------------------------------------------------------------

    #[Route('/category/{id}/show', name: 'app_category_show')]
    public function show(Category $category, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CategoryForm::class , $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('app_category_show', ['id' => $category->getId()]);
        }
        return $this->render('category/show.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }


    //---------------------------------------------------------------------------


    #[Route('/category/{id}/edit', name: 'app_category_edit')]
    public function edit(Request $request, Category $category, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CategoryForm::class , $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('app_category', ['id' => $category->getId()]);
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }


    //---------------------------------------------------------------------------


    #[Route('/category/{id}/delete', name: 'app_category_delete')]
    public function delete(Request $request, Category $category, EntityManagerInterface $manager): Response
    {
        $manager->remove($category);
        $manager->flush();
        return $this->redirectToRoute('app_category');
    }



}
