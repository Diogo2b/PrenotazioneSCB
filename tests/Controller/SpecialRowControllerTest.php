<?php

namespace App\Test\Controller;

use App\Entity\Row;
use App\Entity\Seat;
use App\Entity\Sector;
use App\Entity\Tribune;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SpecialRowControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $rowRepository;
    private EntityRepository $seatRepository;
    private EntityRepository $sectorRepository;
    private string $path = '/admin/special-row/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->rowRepository = $this->manager->getRepository(Row::class);
        $this->seatRepository = $this->manager->getRepository(Seat::class);
        $this->sectorRepository = $this->manager->getRepository(Sector::class);

        foreach ($this->rowRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->seatRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        foreach ($this->sectorRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();


        $user = $this->createUser('admin');
        $this->client->loginUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearDatabase();
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

    public function testNewSpecialRow(): void
    {
        // Create Tribune and Sector
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

        $this->manager->persist($tribune);
        $this->manager->persist($sector);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'special_row[sigle]' => 'Testing Special Row',
            'special_row[capacity]' => 10,
            'special_row[sector]' => $sector->getId(),
        ]);

        self::assertResponseRedirects('/admin/special-row/');

        // Verificar se a Row foi criada
        $row = $this->rowRepository->findOneBy(['sigle' => 'Testing Special Row']);
        self::assertNotNull($row);
        self::assertSame(10, $row->getCapacity());

        // Verificar se os Seats foram criados
        $seats = $this->seatRepository->findBy(['row' => $row]);
        self::assertCount(10, $seats);

        for ($i = 1; $i <= 10; $i++) {
            self::assertNotNull($this->seatRepository->findOneBy(['seatNumber' => $i, 'row' => $row]));
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Rangées');
    }

    private function clearDatabase(): void
    {
        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE row');
        $connection->executeStatement('TRUNCATE TABLE seat');
        $connection->executeStatement('TRUNCATE TABLE sector');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }
}
