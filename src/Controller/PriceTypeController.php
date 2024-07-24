<?php

namespace App\Controller;

use App\Entity\PriceType;
use App\Form\PriceTypeType;
use App\Repository\PriceTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/price/type')]
class PriceTypeController extends AbstractController
{
    #[Route('/', name: 'app_price_type_index', methods: ['GET'])]
    public function index(PriceTypeRepository $priceTypeRepository): Response
    {
        return $this->render('price_type/index.html.twig', [
            'price_types' => $priceTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_price_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $priceType = new PriceType();
        $form = $this->createForm(PriceTypeType::class, $priceType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($priceType);
            $entityManager->flush();

            return $this->redirectToRoute('app_price_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('price_type/new.html.twig', [
            'price_type' => $priceType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_price_type_show', methods: ['GET'])]
    public function show(PriceType $priceType): Response
    {
        return $this->render('price_type/show.html.twig', [
            'price_type' => $priceType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_price_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PriceType $priceType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PriceTypeType::class, $priceType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_price_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('price_type/edit.html.twig', [
            'price_type' => $priceType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_price_type_delete', methods: ['POST'])]
    public function delete(Request $request, PriceType $priceType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $priceType->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($priceType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_price_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
