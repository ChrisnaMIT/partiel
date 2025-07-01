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

    #[Route('/', name: 'app_films')]
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


        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            if (!$film->getId()) {
                $this->addFlash('warning', 'Vous devez d’abord enregistrer le film avant d’ajouter une image.');
                return $this->redirectToRoute('app_film_create');
            }

            $image->setFilm($film);

            $manager->persist($image);
            $manager->flush();

            $this->addFlash('success', 'Image ajoutée avec succès !');

            return $this->redirectToRoute('app_film_edit', ['id' => $film->getId()]);
        }


        return $this->render('film/create.html.twig', [
            'film' => $film,
            'form' => $filmFormCreate->createView(),
            'imageForm' => $imageForm->createView(),

        ]);
    }

    //-----------------------------------------------------------------


    #[Route('/film/show/{id}', name: 'app_film_show')]
    public function showFilm(Film $film, EntityManagerInterface $manager): Response
    {
        $film = $manager->getRepository(Film::class)->createQueryBuilder('f')
            ->leftJoin('f.seances', 's')
            ->addSelect('s')
            ->where('f.id = :id')
            ->setParameter('id', $film->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$film) {
            throw $this->createNotFoundException('Film introuvable.');
        }
        return $this->render('film/show.html.twig', [
            'film' => $film,
        ]);
    }


    //-----------------------------------------------------------------

    #[Route('/film/{id}/edit', name: 'app_film_edit')]
    public function editFilm(Request $request, Film $film, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(FilmForm::class, $film);
        $form->handleRequest($request);


        $image = new Image();
        $imageForm = $this->createForm(ImageForm::class, $image);
        $imageForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', 'Film mis à jour !');
            return $this->redirectToRoute('app_films');
        }

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $image->setFilm($film);
            $manager->persist($image);
            $manager->flush();
            $this->addFlash('success', 'Image ajoutée au film !');
            return $this->redirectToRoute('app_film_edit', ['id' => $film->getId()]);
        }

        return $this->render('film/edit.html.twig', [
            'form' => $form->createView(),
            'imageForm' => $imageForm->createView(),
        ]);
    }


    //-----------------------------------------------------------------


    #[Route('/film/{id}/delete', name: 'app_film_delete')]
    public function deleteFilm(Request $request, Film $film, EntityManagerInterface $manager): Response
    {
        if ($film) {

            $seances = $film->getSeances();
            if ($seances) {
                foreach ($seances as $seance) {
                    if (!$seance) continue;

                    foreach ($seance->getReservations() as $reservation) {
                        $manager->remove($reservation);
                    }
                    foreach ($seance->getSeats() as $seat) {
                        $manager->remove($seat);
                    }
                    $manager->remove($seance);
                }
            }


            $manager->remove($film);
            $manager->flush();

            return $this->redirectToRoute('app_films');
        }

        throw $this->createNotFoundException('Film non trouvé.');
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
