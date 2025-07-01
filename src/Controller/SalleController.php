<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Entity\Seat;
use App\Form\SalleForm;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(SalleRepository $salleRepository): Response
    {
        return $this->render('salle/index.html.twig', [
            'salles' => $salleRepository->findAll(),
        ]);
    }


    //--------------------------------------------------------------

    #[Route('/salle/create', name: 'app_salle_create')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $salle = new Salle();
        $form = $this->createForm(SalleForm::class , $salle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($salle);

            for ($i = 1; $i <= $salle->getCapacity(); $i++) {
                $seat = new Seat();
                $seat->setNumber($i);
                $seat->setIsAvailable(true);
                $seat->setSalle($salle);
                $manager->persist($seat);
            }

            $manager->flush();

            $manager->flush();
            return $this->redirectToRoute('app_salle');
        }
        return $this->render('salle/create.html.twig', [
            'form' => $form->createView(),

        ]);
    }


    //--------------------------------------------------------------

    #[Route('/salle/{id}/edit', name: 'app_salle_edit')]
    public function edit(Request $request, EntityManagerInterface $manager, Salle $salle): Response
    {
        $form = $this->createForm(SalleForm::class , $salle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($salle);
            $manager->flush();
            return $this->redirectToRoute('app_salle');
        }
        return $this->render('salle/edit.html.twig', [
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }

    #[Route('/salle/{id}/delete', name: 'app_salle_delete')]
    public function delete(Request $request, EntityManagerInterface $manager, Salle $salle): Response
    {
        $manager->remove($salle);
        $manager->flush();
        return $this->redirectToRoute('app_salle');
    }





}
