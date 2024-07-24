<?php

namespace App\Test\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\SportMatch;
use App\Entity\PriceType;
use App\Entity\Payment;
use App\Entity\Seat;
use App\Entity\Row;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $ticketRepository;
    private EntityRepository $userRepository;
    private EntityRepository $sportMatchRepository;
    private EntityRepository $priceTypeRepository;
    private EntityRepository $paymentRepository;
    private EntityRepository $seatRepository;
    private string $path = '/admin/ticket/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->ticketRepository = $this->manager->getRepository(Ticket::class);
        $this->userRepository = $this->manager->getRepository(User::class);
        $this->sportMatchRepository = $this->manager->getRepository(SportMatch::class);
        $this->priceTypeRepository = $this->manager->getRepository(PriceType::class);
        $this->paymentRepository = $this->manager->getRepository(Payment::class);
        $this->seatRepository = $this->manager->getRepository(Seat::class);

        foreach ($this->ticketRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->paymentRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->seatRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();

        // Cria e loga um usuário para os testes
        $user = $this->createUser('admin');
        $this->client->loginUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->ticketRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->paymentRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->seatRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    private function createUser(string $emailIdentifier): User
    {
        $user = new User();
        $user->setEmail('testuser' . uniqid($emailIdentifier, true) . '@example.com');
        $user->setRoles(['ROLE_ADMIN']);
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

    private function createRow(): Row
    {
        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $this->manager->persist($row);
        $this->manager->flush();

        return $row;
    }

    private function createSeat(): Seat
    {
        $row = $this->createRow();

        $seat = new Seat();
        $seat->setSeatNumber(1);
        $seat->setRow($row);
        $this->manager->persist($seat);
        $this->manager->flush();

        return $seat;
    }

    private function createPayment(User $user): Payment
    {
        $payment = new Payment();
        $payment->setAmount(20.50);
        $payment->setStatus(true);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());
        $payment->setUser($user);
        $this->manager->persist($payment);
        $this->manager->flush();

        return $payment;
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Tickets');
    }

    public function testNew(): void
    {
        $user = $this->createUser('new');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $seat = $this->createSeat();
        $payment = $this->createPayment($user);

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'ticket[price]' => '20.50',
            'ticket[status]' => true,
            'ticket[user]' => $user->getId(),
            'ticket[sportMatch]' => $sportMatch->getId(),
            'ticket[seat]' => $seat->getId(),
            'ticket[payment]' => $payment->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->ticketRepository->count([]));
    }

    public function testShow(): void
    {
        $user = $this->createUser('show');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $seat = $this->createSeat();
        $payment = $this->createPayment($user);

        $ticket = new Ticket();
        $ticket->setPrice('20.50');
        $ticket->setStatus(true);
        $ticket->setUser($user);
        $ticket->setSportMatch($sportMatch);
        $ticket->setSeat($seat);
        $ticket->setPayment($payment);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($ticket);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $ticket->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Ticket');
        self::assertStringContainsString('20.50', $this->client->getResponse()->getContent());
    }

    public function testEdit(): void
    {
        $user = $this->createUser('edit');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $seat = $this->createSeat();
        $payment = $this->createPayment($user);

        $ticket = new Ticket();
        $ticket->setPrice('20.50');
        $ticket->setStatus(true);
        $ticket->setUser($user);
        $ticket->setSportMatch($sportMatch);
        $ticket->setSeat($seat);
        $ticket->setPayment($payment);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($ticket);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $ticket->getId()));

        $this->client->submitForm('Mettre à jour', [
            'ticket[price]' => '25.00',
            'ticket[status]' => false,
            'ticket[user]' => $user->getId(),
            'ticket[sportMatch]' => $sportMatch->getId(),
            'ticket[seat]' => $seat->getId(),
            'ticket[payment]' => $payment->getId(),
        ]);

        self::assertResponseRedirects($this->path);

        $updatedTicket = $this->ticketRepository->find($ticket->getId());

        self::assertSame('25.00', number_format($updatedTicket->getPrice(), 2, '.', ''));
        self::assertFalse($updatedTicket->isStatus());
    }

    public function testRemove(): void
    {
        $user = $this->createUser('remove');
        $priceType = $this->createPriceType();
        $sportMatch = $this->createSportMatch($priceType);
        $seat = $this->createSeat();
        $payment = $this->createPayment($user);

        $ticket = new Ticket();
        $ticket->setPrice('20.50');
        $ticket->setStatus(true);
        $ticket->setUser($user);
        $ticket->setSportMatch($sportMatch);
        $ticket->setSeat($seat);
        $ticket->setPayment($payment);
        $ticket->setCreatedAt(new \DateTimeImmutable());
        $ticket->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($ticket);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $ticket->getId()));

        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->ticketRepository->count([]));
    }
}
