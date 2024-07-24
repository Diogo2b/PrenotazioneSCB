<?php

namespace App\Controller;

use App\Entity\Row;
use App\Form\RowType;
use App\Repository\RowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/row')]
class RowController extends AbstractController
{
    #[Route('/', name: 'app_row_index', methods: ['GET'])]
    public function index(RowRepository $rowRepository): Response
    {
        return $this->render('row/index.html.twig', [
            'rows' => $rowRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_row_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $row = new Row();
        $form = $this->createForm(RowType::class, $row);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($row);
            $entityManager->flush();

            return $this->redirectToRoute('app_row_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('row/new.html.twig', [
            'row' => $row,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_row_show', methods: ['GET'])]
    public function show(Row $row): Response
    {
        return $this->render('row/show.html.twig', [
            'row' => $row,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_row_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Row $row, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RowType::class, $row);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_row_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('row/edit.html.twig', [
            'row' => $row,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_row_delete', methods: ['POST'])]
    public function delete(Request $request, Row $row, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $row->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($row);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_row_index', [], Response::HTTP_SEE_OTHER);
    }
}
