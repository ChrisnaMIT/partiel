<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\Seat;
use App\Form\SeanceForm;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SeanceController extends AbstractController
{
    #[Route('/seance', name: 'app_seance')]
    public function index(SeanceRepository $seanceRepository): Response
    {
        return $this->render('seance/index.html.twig', [
            'seances' => $seanceRepository->findAll(),
        ]);
    }

    //-----------------------------------------------------------------------


    #[Route('/seance/create', name: 'app_seance_create')]
    public function createSeance(Request $request, EntityManagerInterface $manager): Response
    {
        $seance = new Seance();
        $form = $this->createForm(SeanceForm::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $salle = $seance->getSalle();

            if (!$salle) {
                $this->addFlash('error', 'Salle invalide.');
                return $this->redirectToRoute('app_seance_create');
            }


            $capacity = 45; //
            if ($salle->getId() === 1) {
                $capacity = 85;
            }

            for ($i = 1; $i <= $capacity; $i++) {
                $seat = new Seat();
                $seat->setNumber($i);
                $seat->setReserved(false);
                $seat->setSeance($seance);
                $manager->persist($seat);
            }

            $manager->persist($seance);
            $manager->flush();

            $this->addFlash('success', "Séance créée avec ses $capacity sièges !");

            return $this->redirectToRoute('app_seance');
        }

        return $this->render('seance/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    //-----------------------------------------------------------------------


    #[Route('/seance/{id}/show', name: 'app_seance_show')]
    public function show(Seance $seance, EntityManagerInterface $manager, Film $film): Response
    {
        return $this->render('seance/show.html.twig', [
            'seance' => $seance,
            'films' => $film,
        ]);
    }


    //-----------------------------------------------------------------------


    #[Route('/seance/{id}/edit', name: 'app_seance_edit')]
    public function editSeance(Request $request, EntityManagerInterface $manager, Seance $seance): Response
    {
        $form = $this->createForm(SeanceForm::class , $seance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($seance);
            $manager->flush();
            return $this->redirectToRoute('app_seance_show', ['id' => $seance->getId()]);
        }
        return $this->render('seance/edit.html.twig', [
            'form' => $form->createView(),
            'seance' => $seance,
        ]);
    }


    //-----------------------------------------------------------------------


    #[Route('/seance/{id}/delete', name: 'app_seance_delete')]
    public function deleteSeance(Request $request, EntityManagerInterface $manager, Seance $seance): Response
    {
        if ($seance) {
            foreach ($seance->getReservations() as $reservation) {
                $reservation->setSeance(null);
                $manager->remove($reservation);
            }

            $manager->remove($seance);
            $manager->flush();

            return $this->redirectToRoute('app_seance');
        }

        throw $this->createNotFoundException('Séance non trouvée.');
    }





}
