<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    #[Route('/reservation/validate', name: 'app_reservation_validate', methods: ['POST'])]
    public function validateReservation(Request $request, EntityManagerInterface $manager): Response
    {
        $data = json_decode($request->getContent(), true);
        $seats = $data['seats'] ?? [];
        $seanceId = $data['seanceId'] ?? null;

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous devez être connecté pour réserver des sièges.',
            ], 403);
        }

        if (empty($seats) || !$seanceId) {
            return new JsonResponse(['success' => false, 'message' => 'Séance ou sièges manquants.'], 400);
        }

        $seance = $manager->getRepository(Seance::class)->find($seanceId);
        if (!$seance) {
            return new JsonResponse(['success' => false, 'message' => 'Séance introuvable.'], 404);
        }

        foreach ($seats as $seatNumber) {
            $seat = $manager->getRepository(\App\Entity\Seat::class)->findOneBy([
                'salle' => $seance->getSalle(),
                'number' => $seatNumber,
            ]);
            if (!$seat) {
                return new JsonResponse(['success' => false, 'message' => "Le siège $seatNumber n'existe pas."], 400);
            }
            if ($seat->isReserved() || !$seat->isAvailable()) {
                return new JsonResponse(['success' => false, 'message' => "Le siège $seatNumber est déjà réservé ou pris par un employé."], 400);
            }

            $seat->setReserved(true);
            $manager->persist($seat);
        }


        $reservation = new Reservation();
        $reservation->setReservationUser($user);
        $reservation->setSeance($seance);
        $reservation->setSeats($seats);
        $reservation->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($reservation);

        $manager->flush();

        return new JsonResponse([
            'success' => true,
            'redirectUrl' => $this->generateUrl('stripe_checkout', [
                'reservationId' => $reservation->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }




    //-----------------------------------------------------------------------

    #[Route('/reservation/{id}', name: 'app_reservation_select_seats')]
    public function selectSeats(Seance $seance, EntityManagerInterface $manager, Security $security): Response
    {
        $salle = $seance->getSalle();
        $seats = $salle->getSeats()->toArray();
        usort($seats, fn($a, $b) => $a->getNumber() <=> $b->getNumber());

        $user = $security->getUser();
        $reservation = null;

        if ($user) {
            $reservation = $manager->getRepository(Reservation::class)->findOneBy([
                'seance' => $seance,
                'reservationUser' => $user,
            ]);
        }

        $reservedSeatsEmployee = $manager->getRepository(\App\Entity\Seat::class)->findBy([
            'salle' => $salle,
            'isAvailable' => false,
        ]);
        $reservedSeatIds = array_map(fn($seat) => $seat->getId(), $reservedSeatsEmployee);

        foreach ($seance->getReservations() as $existingReservation) {

            foreach ($existingReservation->getSeats() as $seatNumber) {
                $seat = $manager->getRepository(\App\Entity\Seat::class)->findOneBy([
                    'salle' => $salle,
                    'number' => $seatNumber,
                ]);
                if ($seat) {
                    $reservedSeatIds[] = $seat->getId();
                }
            }
        }


        $reservedSeatIds = array_unique($reservedSeatIds);

        return $this->render('reservation/select_seats.html.twig', [
            'seance' => $seance,
            'seats' => $seats,
            'salle' => $salle,
            'capacity' => count($seats),
            'reservedSeatIds' => $reservedSeatIds,

            'reservation' => $reservation ?: null,
        ]);
    }



    //-----------------------------------------------------------------------


    #[Route('/reservation/cancel/{id}', name: 'reservation_cancel')]
    public function cancelReservation(Reservation $reservation, EntityManagerInterface $manager, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user || $reservation->getReservationUser() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à annuler cette réservation.');
            return $this->redirectToRoute('app_reservation_select_seats', [
                'id' => $reservation->getSeance()->getId(),
            ]);
        }

        $seanceDate = $reservation->getSeance()->getDate();
        $seanceTime = $reservation->getSeance()->getStartTime();
        $seanceDateTime = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $seanceDate->format('Y-m-d') . ' ' . $seanceTime->format('H:i:s')
        );

        if (!$seanceDateTime) {
            throw new \RuntimeException('Impossible de construire la date et l\'heure de la séance.');
        }

        $now = new \DateTimeImmutable();
        $interval = $seanceDateTime->getTimestamp() - $now->getTimestamp();

        if ($interval <= 600) {
            $this->addFlash('error', 'Annulation impossible : moins de 10 minutes avant le début de la séance.');
            return $this->redirectToRoute('app_reservation_select_seats', [
                'id' => $reservation->getSeance()->getId(),
            ]);
        }



        $seance = $reservation->getSeance();
        $numSeats = count($reservation->getSeats());
        $seance->setPlaceAvailable($seance->getPlaceAvailable() + $numSeats);


        foreach ($reservation->getSeats() as $seatNumber) {
            $seat = $manager->getRepository(\App\Entity\Seat::class)->findOneBy([
                'salle' => $seance->getSalle(),
                'number' => $seatNumber,
            ]);
            if ($seat) {
                $seat->setIsAvailable(true);
                $seat->setReserved(false);
                $manager->persist($seat);

            }
        }

        $manager->remove($reservation);
        dump('Removing reservation');
        $manager->flush();
        dump('Flush done');



        $this->addFlash('success', 'Votre réservation a été annulée et vos places ont été libérées.');
        return $this->redirectToRoute('app_reservation_select_seats', [
            'id' => $seance->getId(),
        ]);

    }


}
