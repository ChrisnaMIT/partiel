<?php

namespace App\Controller;

use App\Entity\Seance;
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
        $form = $this->createForm(SeanceForm::class , $seance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($seance);
            $manager->flush();
            return $this->redirectToRoute('app_seance');
        }
        return $this->render('seance/create.html.twig', [
            'form' => $form->createView(),

        ]);
    }


    //-----------------------------------------------------------------------


    #[Route('/seance/{id}/show', name: 'app_seance_show')]
    public function show(Seance $seance, EntityManagerInterface $manager): Response
    {
        return $this->render('seance/show.html.twig', [
            'seance' => $seance,
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
        $manager->remove($seance);
        $manager->flush();
        return $this->redirectToRoute('app_seance');
    }



}
