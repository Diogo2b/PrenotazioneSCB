<?php

namespace App\Test\Controller;

use App\Entity\Row;
use App\Entity\Sector;
use App\Entity\Tribune;
use App\Entity\Seat; // Add this line
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RowControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/row/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Row::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Row index');
    }

    public function testNew(): void
    {
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

        $this->client->submitForm('Save', [
            'row[sigle]' => 'Testing Row',
            'row[capacity]' => 10,
            'row[sector]' => $sector->getId(),
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
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

        $fixture = new Row();
        $fixture->setSigle('My Row');
        $fixture->setCapacity(20);
        $fixture->setSector($sector);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Row');

        $crawler = $this->client->getCrawler();
        self::assertGreaterThan(0, $crawler->filter('td:contains("My Row")')->count());
    }

    public function testEdit(): void
    {
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

        $fixture = new Row();
        $fixture->setSigle('Value');
        $fixture->setCapacity(30);
        $fixture->setSector($sector);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'row[sigle]' => 'Something New',
            'row[capacity]' => 40,
            'row[sector]' => $sector->getId(),
        ]);

        self::assertResponseRedirects('/row/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getSigle());
        self::assertSame(40, $fixture[0]->getCapacity());
        self::assertSame($sector->getId(), $fixture[0]->getSector()->getId());
    }

    public function testRemove(): void
    {
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

        $row = new Row();
        $row->setSigle('Value');
        $row->setCapacity(30);
        $row->setSector($sector);

        $seat1 = new Seat();
        $seat1->setSeatNumber(1);
        $seat1->setRow($row);

        $seat2 = new Seat();
        $seat2->setSeatNumber(2);
        $seat2->setRow($row);

        $this->manager->persist($row);
        $this->manager->persist($seat1);
        $this->manager->persist($seat2);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $row->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/row/');
        self::assertSame(0, $this->repository->count([]));
        self::assertSame(0, $this->manager->getRepository(Seat::class)->count([]));
    }
}
