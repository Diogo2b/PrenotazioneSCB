<?php

namespace App\Controller;

use App\Entity\PaymentTicket;
use App\Form\PaymentTicketType;
use App\Repository\PaymentTicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment/ticket')]
class PaymentTicketController extends AbstractController
{
    #[Route('/', name: 'app_payment_ticket_index', methods: ['GET'])]
    public function index(PaymentTicketRepository $paymentTicketRepository): Response
    {
        return $this->render('payment_ticket/index.html.twig', [
            'payment_tickets' => $paymentTicketRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_payment_ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $paymentTicket = new PaymentTicket();
        $form = $this->createForm(PaymentTicketType::class, $paymentTicket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($paymentTicket);
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment_ticket/new.html.twig', [
            'payment_ticket' => $paymentTicket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_payment_ticket_show', methods: ['GET'])]
    public function show(PaymentTicket $paymentTicket): Response
    {
        return $this->render('payment_ticket/show.html.twig', [
            'payment_ticket' => $paymentTicket,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_payment_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PaymentTicket $paymentTicket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaymentTicketType::class, $paymentTicket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment_ticket/edit.html.twig', [
            'payment_ticket' => $paymentTicket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payment_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, PaymentTicket $paymentTicket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $paymentTicket->getId(), $request->request->get('_token'))) {
            $entityManager->remove($paymentTicket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_payment_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
