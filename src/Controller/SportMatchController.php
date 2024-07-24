<?php

namespace App\Controller;

use App\Entity\SportMatch;
use App\Form\SportMatchType;
use App\Repository\SportMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/sport/match')]
class SportMatchController extends AbstractController
{
    #[Route('/', name: 'app_sport_match_index', methods: ['GET'])]
    public function index(SportMatchRepository $sportMatchRepository): Response
    {
        return $this->render('sport_match/index.html.twig', [
            'sport_matches' => $sportMatchRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sport_match_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sportMatch = new SportMatch();
        $form = $this->createForm(SportMatchType::class, $sportMatch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sportMatch);
            $entityManager->flush();

            return $this->redirectToRoute('app_sport_match_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sport_match/new.html.twig', [
            'sport_match' => $sportMatch,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sport_match_show', methods: ['GET'])]
    public function show(SportMatch $sportMatch): Response
    {
        return $this->render('sport_match/show.html.twig', [
            'sport_match' => $sportMatch,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sport_match_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SportMatch $sportMatch, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SportMatchType::class, $sportMatch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sport_match_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sport_match/edit.html.twig', [
            'sport_match' => $sportMatch,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sport_match_delete', methods: ['POST'])]
    public function delete(Request $request, SportMatch $sportMatch, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sportMatch->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sportMatch);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sport_match_index', [], Response::HTTP_SEE_OTHER);
    }
}
