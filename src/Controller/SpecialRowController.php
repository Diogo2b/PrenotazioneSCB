<?php

// src/Controller/SpecialRowController.php

namespace App\Controller;

use App\Entity\Row;
use App\Entity\Seat;
use App\Form\SpecialRowType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/special-row')]
class SpecialRowController extends AbstractController
{
    #[Route('/new', name: 'app_special_row_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $row = new Row();
        $form = $this->createForm(SpecialRowType::class, $row);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($row);
            $entityManager->flush();

            $numberOfSeats = $form->get('capacity')->getData();
            for ($i = 1; $i <= $numberOfSeats; $i++) {
                $seat = new Seat();
                $seat->setSeatNumber($i);
                $seat->setRow($row);
                $entityManager->persist($seat);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_special_row_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('special_row/new.html.twig', [
            'row' => $row,
            'form' => $form,
        ]);
    }

    #[Route('/', name: 'app_special_row_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $rows = $entityManager->getRepository(Row::class)->findAll();

        return $this->render('special_row/index.html.twig', [
            'rows' => $rows,
        ]);
    }
}
