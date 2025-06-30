<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    //-----------------------------------------------------------------------

    #[Route('/reservation/{id}', name: 'app_reservation_select_seats')]
    public function selectSeats(Seance $seance): Response
    {
        $salle = $seance->getSalle();
        $capacity = $salle->getCapacity();

        return $this->render('reservation/select_seats.html.twig', [
            'seance' => $seance,
            'salle' => $salle,
            'capacity' => $capacity,
        ]);
    }

    //-----------------------------------------------------------------------






}
