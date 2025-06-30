<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Image;
use App\Form\FilmForm;
use App\Form\ImageForm;
use App\Repository\FilmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FilmController extends AbstractController
{

    #[Route('/film', name: 'app_films')]
    public function index(FilmRepository $filmRepository): Response
    {
        return $this->render('film/index.html.twig', [
            'films' => $filmRepository->findAll(),
        ]);
    }


    //-----------------------------------------------------------------


    #[Route('/film/create', name: 'app_film_create')]
    public function createFilm(Request $request, EntityManagerInterface $manager): Response
    {
        $film = new Film();
        $filmFormCreate = $this ->createForm(FilmForm::class , $film);
        $filmFormCreate->handleRequest($request);

        $image = new Image();
        $imageForm = $this->createForm(ImageForm::class, $image);
        $imageForm->handleRequest($request);


        if ($filmFormCreate->isSubmitted() && $filmFormCreate->isValid()) {
            $manager->persist($film);
            $manager->flush();
            return $this->redirectToRoute('app_films');
        }
        return $this->render('film/create.html.twig', [
            'film' => $film,
            'form' => $filmFormCreate->createView(),
            'imageForm' => $imageForm->createView(),

        ]);
    }

    //-----------------------------------------------------------------


    #[Route('/film/show/{id}', name: 'app_film_show')]
    public function showFilm(Film $film): Response
    {
        return $this->render('film/show.html.twig', [
            'film' => $film,
        ]);
    }


    //-----------------------------------------------------------------

    #[Route('/film/{id}/edit', name: 'app_film_edit')]
    public function editFilm(Request $request, Film $film, EntityManagerInterface $manager): Response
    {
        $image = new Image();
        $imageForm = $this->createForm(ImageForm::class, $image);
        $imageForm->handleRequest($request);

        $filmFormEdit = $this ->createForm(FilmForm::class , $film);
        $filmFormEdit->handleRequest($request);
        if ($filmFormEdit->isSubmitted() && $filmFormEdit->isValid()) {
            $manager->persist($film);
            $manager->flush();
            return $this->redirectToRoute('app_films');
        }
        return $this->render('film/edit.html.twig', [
            'film' => $film,
            'form' => $filmFormEdit->createView(),
            'imageForm' => $imageForm->createView(),
        ]);
    }

    //-----------------------------------------------------------------


    #[Route('/film/{id}/delete', name: 'app_film_delete')]
    public function deleteFilm(Request $request, Film $film, EntityManagerInterface $manager): Response
    {

        if ($film){
            foreach ($film->getImages() as $image){
                $manager->remove($image);
            }
        }
        $manager->remove($film);
        $manager->flush();
        return $this->redirectToRoute('app_films');
    }


    //-----------------------------------------------------------------

    #[Route('film/{id}/images', name: 'app_film_images')]
    public function addFilmImage(Request $request, Film $film, EntityManagerInterface $manager): Response
    {
        $image = new Image();
        $imageForm = $this ->createForm(ImageForm::class , $image);
        $imageForm->handleRequest($request);
        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $image->setFilm($film);
            $manager->persist($image);
            $manager->flush();
            return $this->redirectToRoute('app_film_images', ['id' => $film->getId()]);
        }
        return $this->render('film/image.html.twig', [
            'film' => $film,
            'form' => $imageForm->createView(),
            'imageForm' => $imageForm->createView(),
        ]);
    }


    //-----------------------------------------------------------------

    #[Route('film/{id}/image/delete', name: 'app_film_image_delete')]
    public function deleteFilmImage(Request $request, Film $film, EntityManagerInterface $manager, Image $image): Response
    {
        $film = $image->getFilm();
        $manager->remove($image);
        $manager->flush();
        return $this->redirectToRoute('app_film_images', ['id' => $film->getId()]);
    }





}
