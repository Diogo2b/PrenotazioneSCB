<?php

namespace App\Controller;

use App\Entity\Tribune;
use App\Form\TribuneType;
use App\Repository\TribuneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tribune')]
class TribuneController extends AbstractController
{
    #[Route('/', name: 'app_tribune_index', methods: ['GET'])]
    public function index(TribuneRepository $tribuneRepository, EntityManagerInterface $entityManager): Response
    {
        $tribunes = $tribuneRepository->findAll();

        // Calculate the total number of seats for each tribune
        foreach ($tribunes as $tribune) {
            $seatsCount = 0;
            foreach ($tribune->getSectors() as $sector) {
                foreach ($sector->getListRow() as $row) {
                    $seatsCount += count($row->getSeats());
                }
            }
            $tribune->seatsCount = $seatsCount; // Temporary property to store seats count
        }

        return $this->render('tribune/index.html.twig', [
            'tribunes' => $tribunes,
        ]);
    }

    #[Route('/new', name: 'app_tribune_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tribune = new Tribune();
        $form = $this->createForm(TribuneType::class, $tribune);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tribune);
            $entityManager->flush();

            return $this->redirectToRoute('app_tribune_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tribune/new.html.twig', [
            'tribune' => $tribune,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tribune_show', methods: ['GET'])]
    public function show(Tribune $tribune): Response
    {
        return $this->render('tribune/show.html.twig', [
            'tribune' => $tribune,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tribune_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tribune $tribune, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TribuneType::class, $tribune);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tribune_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tribune/edit.html.twig', [
            'tribune' => $tribune,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tribune_delete', methods: ['POST'])]
    public function delete(Request $request, Tribune $tribune, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tribune->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tribune);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tribune_index', [], Response::HTTP_SEE_OTHER);
    }
}
