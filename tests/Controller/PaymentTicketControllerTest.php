<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\PaymentTicket;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PaymentTicketRepository;

class PaymentTicketControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $repository;
    private $path = '/payment/ticket/';

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->entityManager->getRepository(PaymentTicket::class);
        $this->resetDatabase();
        $this->loadFixtures();
    }

    private function resetDatabase()
    {
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();

        $tables = $schemaManager->listTableNames();
        foreach ($tables as $table) {
            $connection->executeQuery("SET FOREIGN_KEY_CHECKS=0");
            $connection->executeQuery("TRUNCATE TABLE $table");
            $connection->executeQuery("SET FOREIGN_KEY_CHECKS=1");
        }
    }

    private function loadFixtures()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('password');
        $user->setUsername('testuser');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($user);

        $payment = new Payment();
        $payment->setAmount(100.00);
        $payment->setStatus(true);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());
        $payment->setUser($user);
        $this->entityManager->persist($payment);

        $ticket = new Ticket();
        $ticket->setPrice(50.00);
        $ticket->setStatus(true);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setUpdatedAt(new \DateTimeImmutable());
        $ticket->setUser($user);
        $this->entityManager->persist($ticket);

        $this->entityManager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des Paiements des Billets');
    }

    public function testNew(): void
    {
        $crawler = $this->client->request('GET', $this->path . 'new');

        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Enregistrer')->form([
            'payment_ticket[payment]' => 1,
            'payment_ticket[ticket]' => 1,
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/payment/ticket/');
    }

    public function testShow(): void
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find(1);
        $ticket = $this->entityManager->getRepository(Ticket::class)->find(1);

        $paymentTicket = new PaymentTicket();
        $paymentTicket->setPayment($payment);
        $paymentTicket->setTicket($ticket);
        $this->entityManager->persist($paymentTicket);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $this->path . $paymentTicket->getId() . '/show');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'PaymentTicket');
    }

    public function testEdit(): void
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find(1);
        $ticket = $this->entityManager->getRepository(Ticket::class)->find(1);

        $paymentTicket = new PaymentTicket();
        $paymentTicket->setPayment($payment);
        $paymentTicket->setTicket($ticket);
        $this->entityManager->persist($paymentTicket);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $this->path . $paymentTicket->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Mettre à jour')->form([
            'payment_ticket[payment]' => 1,
            'payment_ticket[ticket]' => 1,
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/payment/ticket/');

        $updatedPaymentTicket = $this->repository->find($paymentTicket->getId());

        $this->assertSame(1, $updatedPaymentTicket->getPayment()->getId());
        $this->assertSame(1, $updatedPaymentTicket->getTicket()->getId());
    }

    public function testDelete(): void
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find(1);
        $ticket = $this->entityManager->getRepository(Ticket::class)->find(1);

        $paymentTicket = new PaymentTicket();
        $paymentTicket->setPayment($payment);
        $paymentTicket->setTicket($ticket);
        $this->entityManager->persist($paymentTicket);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $this->path . $paymentTicket->getId() . '/show');
        $this->assertResponseIsSuccessful();

        $deleteForm = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($deleteForm);

        $this->assertResponseRedirects('/payment/ticket/');

        $deletedPaymentTicket = $this->repository->find($paymentTicket->getId());
        $this->assertNull($deletedPaymentTicket);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
