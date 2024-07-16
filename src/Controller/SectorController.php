<?php

namespace App\Controller;

use App\Entity\Sector;
use App\Form\SectorType;
use App\Repository\SectorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sector')]
class SectorController extends AbstractController
{
    #[Route('/', name: 'app_sector_index', methods: ['GET'])]
    public function index(SectorRepository $sectorRepository): Response
    {
        return $this->render('sector/index.html.twig', [
            'sectors' => $sectorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sector_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sector = new Sector();
        $form = $this->createForm(SectorType::class, $sector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sector);
            $entityManager->flush();

            return $this->redirectToRoute('app_sector_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sector/new.html.twig', [
            'sector' => $sector,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sector_show', methods: ['GET'])]
    public function show(Sector $sector): Response
    {
        return $this->render('sector/show.html.twig', [
            'sector' => $sector,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sector_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sector $sector, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SectorType::class, $sector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sector_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sector/edit.html.twig', [
            'sector' => $sector,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sector_delete', methods: ['POST'])]
    public function delete(Request $request, Sector $sector, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sector->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sector);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sector_index', [], Response::HTTP_SEE_OTHER);
    }
}
