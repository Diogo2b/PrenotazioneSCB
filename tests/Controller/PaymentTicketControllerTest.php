<?php

namespace App\Test\Controller;

use App\Entity\Payment;
use App\Entity\PaymentTicket;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\PriceType;
use App\Entity\SportMatch;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentTicketControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $paymentTicketRepository;
    private EntityRepository $paymentRepository;
    private EntityRepository $ticketRepository;
    private EntityRepository $userRepository;
    private EntityRepository $priceTypeRepository;
    private EntityRepository $sportMatchRepository;
    private string $path = '/payment/ticket/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->paymentTicketRepository = $this->manager->getRepository(PaymentTicket::class);
        $this->paymentRepository = $this->manager->getRepository(Payment::class);
        $this->ticketRepository = $this->manager->getRepository(Ticket::class);
        $this->userRepository = $this->manager->getRepository(User::class);
        $this->priceTypeRepository = $this->manager->getRepository(PriceType::class);
        $this->sportMatchRepository = $this->manager->getRepository(SportMatch::class);

        foreach ($this->paymentTicketRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->paymentRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->ticketRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->priceTypeRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->sportMatchRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    private function createUser(string $emailIdentifier): User
    {
        $user = new User();
        $user->setEmail('testuser' . uniqid($emailIdentifier, true) . '@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('testpassword');
        $user->setUsername('testusername' . uniqid($emailIdentifier, true));
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    private function createPriceType(): PriceType
    {
        $priceType = new PriceType();
        $priceType->setName('Test Price Type');
        $priceType->setPrice(100);
        $this->manager->persist($priceType);
        $this->manager->flush();

        return $priceType;
    }

    private function createSportMatch(PriceType $priceType): SportMatch
    {
        $sportMatch = new SportMatch();
        $sportMatch->setHomeTeam('Home Team');
        $sportMatch->setAwayTeam('Away Team');
        $sportMatch->setMatchDate(new \DateTime('2024-07-20'));
        $sportMatch->setCreatedAt(new \DateTimeImmutable());
        $sportMatch->setUpdatedAt(new \DateTimeImmutable());
        $sportMatch->setPriceType($priceType);
        $this->manager->persist($sportMatch);
        $this->manager->flush();

        return $sportMatch;
    }

    private function createPayment(User $user): Payment
    {
        $payment = new Payment();
        $payment->setAmount('20.50');
        $payment->setStatus(true);
        $payment->setUser($user);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($payment);
        $this->manager->flush();

        // Verificação explícita
        $payment = $this->paymentRepository->find($payment->getId());
        if (!$payment) {
            throw new \Exception('Payment not found after creation');
        }

        return $payment;
    }

    private function createTicket(User $user, SportMatch $sportMatch): Ticket
    {
        $ticket = new Ticket();
        $ticket->setPrice('20.50');
        $ticket->setStatus(true);
        $ticket->setUser($user);
        $ticket->setSportMatch($sportMatch);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($ticket);
        $this->manager->flush();

        // Verificação explícita
        $ticket = $this->ticketRepository->find($ticket->getId());
        if (!$ticket) {
            throw new \Exception('Ticket not found after creation');
        }

        return $ticket;
    }

    public function testIndex(): void
    {
        $user = $this->createUser('index');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $payment = $this->createPayment($user);
        $ticket = $this->createTicket($user, $sportMatch);

        $paymentTicket = new PaymentTicket();
        $paymentTicket->setPayment($payment);
        $paymentTicket->setTicket($ticket);

        $this->manager->persist($paymentTicket);
        $this->manager->flush();

        $this->client->request('GET', $this->path);
        $this->client->followRedirect();  // Adicionado para seguir o redirecionamento

        // Verificar se a resposta está correta
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des PaymentTickets');
    }

    public function testNew(): void
    {
        $user = $this->createUser('new');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $payment = $this->createPayment($user);
        $ticket = $this->createTicket($user, $sportMatch);

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'payment_ticket[payment]' => $payment->getId(),
            'payment_ticket[ticket]' => $ticket->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->paymentTicketRepository->count([]));
    }

    public function testShow(): void
    {
        $user = $this->createUser('show');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $payment = $this->createPayment($user);
        $ticket = $this->createTicket($user, $sportMatch);

        $paymentTicket = new PaymentTicket();
        $paymentTicket->setPayment($payment);
        $paymentTicket->setTicket($ticket);

        $this->manager->persist($paymentTicket);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $paymentTicket->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('PaymentTicket');

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString((string)$paymentTicket->getId(), $content);
        self::assertStringContainsString((string)$payment->getId(), $content);
        self::assertStringContainsString((string)$ticket->getId(), $content);
    }

    public function testEdit(): void
    {
        $user = $this->createUser('edit');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $payment = $this->createPayment($user);
        $ticket = $this->createTicket($user, $sportMatch);

        $paymentTicket = new PaymentTicket();
        $paymentTicket->setPayment($payment);
        $paymentTicket->setTicket($ticket);

        $this->manager->persist($paymentTicket);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $paymentTicket->getId()));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Update', [
            'payment_ticket[payment]' => $payment->getId(),
            'payment_ticket[ticket]' => $ticket->getId(),
        ]);

        self::assertResponseRedirects($this->path);

        $updatedPaymentTicket = $this->paymentTicketRepository->find($paymentTicket->getId());
        self::assertSame($payment->getId(), $updatedPaymentTicket->getPayment()->getId());
        self::assertSame($ticket->getId(), $updatedPaymentTicket->getTicket()->getId());
    }

    public function testRemove(): void
    {
        $user = $this->createUser('remove');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $payment = $this->createPayment($user);
        $ticket = $this->createTicket($user, $sportMatch);

        $paymentTicket = new PaymentTicket();
        $paymentTicket->setPayment($payment);
        $paymentTicket->setTicket($ticket);

        $this->manager->persist($paymentTicket);
        $this->manager->flush();

        $originalCount = $this->paymentTicketRepository->count([]);

        $this->client->request('GET', sprintf('%s%s', $this->path, $paymentTicket->getId()));
        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects($this->path);
        self::assertSame($originalCount - 1, $this->paymentTicketRepository->count([]));
    }
}
