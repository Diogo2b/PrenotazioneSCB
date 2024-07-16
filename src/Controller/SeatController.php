<?php

namespace App\Controller;

use App\Entity\Seat;
use App\Form\SeatType;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/seat')]
class SeatController extends AbstractController
{
    #[Route('/', name: 'app_seat_index', methods: ['GET'])]
    public function index(SeatRepository $seatRepository): Response
    {
        return $this->render('seat/index.html.twig', [
            'seats' => $seatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_seat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $seat = new Seat();
        $form = $this->createForm(SeatType::class, $seat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($seat);
            $entityManager->flush();

            return $this->redirectToRoute('app_seat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('seat/new.html.twig', [
            'seat' => $seat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_seat_show', methods: ['GET'])]
    public function show(Seat $seat): Response
    {
        return $this->render('seat/show.html.twig', [
            'seat' => $seat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_seat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Seat $seat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeatType::class, $seat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_seat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('seat/edit.html.twig', [
            'seat' => $seat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_seat_delete', methods: ['POST'])]
    public function delete(Request $request, Seat $seat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$seat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($seat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_seat_index', [], Response::HTTP_SEE_OTHER);
    }
}
