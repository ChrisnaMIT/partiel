<?php

namespace App\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripeController extends AbstractController
{
    #[Route('/checkout/{reservationId}', name: 'stripe_checkout')]
    public function checkout(int $reservationId, EntityManagerInterface $manager, UrlGeneratorInterface $urlGenerator): Response
    {

        $reservation = $manager->getRepository(Reservation::class)->find($reservationId);
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }


        $seatPrice = 750;
        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Réservation de sièges',
                        'description' => 'Sièges : ' . implode(', ', $reservation->getSeats()),
                    ],
                    'unit_amount' => $seatPrice,
                ],
                'quantity' => count($reservation->getSeats()),
            ],
        ];


        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $urlGenerator->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $urlGenerator->generate('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);


        return $this->redirect($session->url, 303);
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function success(): Response
    {

        return $this->render('stripe/success.html.twig');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {

        return $this->render('stripe/cancel.html.twig');
    }
}
