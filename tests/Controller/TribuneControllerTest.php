<?php

namespace App\Test\Controller;

use App\Entity\Tribune;
use App\Entity\Sector;
use App\Entity\Row;
use App\Entity\Seat;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TribuneControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/admin/tribune/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Tribune::class);
        $this->clearDatabase();

        $user = $this->createUser('admin');
        $this->client->loginUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearDatabase();
    }

    private function clearDatabase(): void
    {
        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE tribune');
        $connection->executeStatement('TRUNCATE TABLE sector');
        $connection->executeStatement('TRUNCATE TABLE row');
        $connection->executeStatement('TRUNCATE TABLE seat');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
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

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Tribunes');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'tribune[name]' => 'Testing',
            'tribune[sigle]' => 'TS',
            'tribune[numbered_seats]' => true,
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new Tribune();
        $fixture->setName('My Title');
        $fixture->setSigle('MT');
        $fixture->setNumberedSeats(true);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Détails de la Tribune');

        self::assertStringContainsString('My Title', $crawler->filter('body')->text());
        self::assertStringContainsString('MT', $crawler->filter('body')->text());
        self::assertStringContainsString('Oui', $crawler->filter('body')->text());
    }

    public function testEdit(): void
    {
        $fixture = new Tribune();
        $fixture->setName('Value');
        $fixture->setSigle('VL');
        $fixture->setNumberedSeats(false);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Mettre à jour', [
            'tribune[name]' => 'Something New',
            'tribune[sigle]' => 'SN',
            'tribune[numbered_seats]' => true,
        ]);

        self::assertResponseRedirects($this->path);

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('SN', $fixture[0]->getSigle());
        self::assertTrue($fixture[0]->isNumberedSeats());
    }

    public function testRemove(): void
    {
        // Criação das entidades
        $tribune = new Tribune();
        $tribune->setName('Testing Tribune');
        $tribune->setSigle('TT');
        $tribune->setNumberedSeats(true);

        $sector = new Sector();
        $sector->setName('Testing Sector');
        $sector->setSigle('TS');
        $sector->setNumberedSeats(true);
        $sector->setCapacity(100);
        $sector->setAvailableForSale(true);
        $sector->setTribune($tribune);
        $tribune->getSectors()->add($sector);

        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $row->setSector($sector);
        $sector->getListRow()->add($row);

        $seat = new Seat();
        $seat->setSeatNumber(1);
        $seat->setRow($row);
        $row->getSeats()->add($seat);

        $this->manager->persist($tribune);
        $this->manager->flush();

        self::assertSame(1, $this->manager->getRepository(Tribune::class)->count([]));
        self::assertSame(1, $this->manager->getRepository(Sector::class)->count([]));
        self::assertSame(1, $this->manager->getRepository(Row::class)->count([]));
        self::assertSame(1, $this->manager->getRepository(Seat::class)->count([]));

        // Remove Tribune
        $this->client->request('GET', sprintf('%s%s', $this->path, $tribune->getId()));
        $this->client->submitForm('Supprimer', [], 'POST');
        $this->manager->flush();

        // Vérifier que la Tribune a été supprimée
        self::assertSame(0, $this->manager->getRepository(Tribune::class)->count([]));

        // Vérifier que toutes les entités liées ont été supprimées
        self::assertSame(0, $this->manager->getRepository(Sector::class)->count([]));
        self::assertSame(0, $this->manager->getRepository(Row::class)->count([]));
        self::assertSame(0, $this->manager->getRepository(Seat::class)->count([]));
    }
}
