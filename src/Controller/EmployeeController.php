<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\Seat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmployeeController extends AbstractController
{
    #[Route('/employee/seances', name: 'employee_seances')]
    public function seances(EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_EMPLOYER')) {
            $this->addFlash('danger', 'Accès réservé aux employés.');
            return $this->redirectToRoute('app_home');
        }

        $seances = $em->getRepository(Seance::class)->findAll();

        return $this->render('employee/seances.html.twig', [
            'seances' => $seances,
        ]);
    }



    #[Route('/employee/seance/{id}/book', name: 'employee_book_seats')]
    public function bookSeats(Seance $seance, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_EMPLOYER')) {
            $this->addFlash('danger', 'Accès réservé aux employés.');
            return $this->redirectToRoute('app_films');
        }


        $salle = $seance->getSalle();
        $seats = $manager->getRepository(Seat::class)->findBy(['salle' => $salle]);
        usort($seats, fn($a, $b) => $a->getNumber() <=> $b->getNumber());

        $reservations = $seance->getReservations();
        $reservedSeatIds = [];

        foreach ($reservations as $reservation) {
            foreach ($reservation->getSeats() as $seatNumber) {
                $seat = $manager->getRepository(Seat::class)->findOneBy([
                    'salle' => $salle,
                    'number' => $seatNumber,
                ]);
                if ($seat) {
                    $reservedSeatIds[] = $seat->getId();
                }
            }
        }




        return $this->render('employee/book_seats.html.twig', [
            'seance' => $seance,
            'seats' => $seats,
            'reservedSeatIds' => $reservedSeatIds,
        ]);


    }



    #[Route('/employee/seance/{id}/confirm-booking', name: 'employee_confirm_booking', methods: ['POST'])]
    public function confirmBooking(Request $request, Seance $seance, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_EMPLOYER')) {
            return new Response(['success' => false, 'message' => 'Accès interdit'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $seatIds = $data['seats'] ?? [];

        foreach ($seatIds as $seatId) {
            $seat = $manager->getRepository(Seat::class)->find($seatId);
            if ($seat && $seat->isAvailable()) {
                $seat->setIsAvailable(false);

            }
        }

        $manager->flush();

        return new Response(['success' => true]);
    }


}

