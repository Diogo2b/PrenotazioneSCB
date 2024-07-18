<?php

namespace App\Test\Controller;

use App\Entity\Tribune;
use App\Entity\Sector;
use App\Entity\Row;
use App\Entity\Seat;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TribuneControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/tribune/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Tribune::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Tribune index');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
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
        self::assertPageTitleContains('Tribune');

        $this->assertGreaterThan(0, $crawler->filter('td:contains("My Title")')->count());
        $this->assertGreaterThan(0, $crawler->filter('td:contains("MT")')->count());
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Yes")')->count());
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

        $this->client->submitForm('Update', [
            'tribune[name]' => 'Something New',
            'tribune[sigle]' => 'SN',
            'tribune[numbered_seats]' => true,
        ]);

        self::assertResponseRedirects('/tribune/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('SN', $fixture[0]->getSigle());
        self::assertTrue($fixture[0]->isNumberedSeats());
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

        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $row->setSector($sector);

        $seat = new Seat();
        $seat->setSeatNumber(1);
        $seat->setRow($row);

        $this->manager->persist($tribune);
        $this->manager->persist($sector);
        $this->manager->persist($row);
        $this->manager->persist($seat);
        $this->manager->flush();

        // Verificar que todas as entidades foram persistidas
        self::assertSame(1, $this->manager->getRepository(Tribune::class)->count([]));
        self::assertSame(1, $this->manager->getRepository(Sector::class)->count([]));
        self::assertSame(1, $this->manager->getRepository(Row::class)->count([]));
        self::assertSame(1, $this->manager->getRepository(Seat::class)->count([]));

        // Remover a Tribune
        $this->client->request('GET', sprintf('%s%s', $this->path, $tribune->getId()));
        $this->client->submitForm('Delete');

        // Verificar que todas as entidades foram removidas
        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->manager->getRepository(Tribune::class)->count([]));
        self::assertSame(0, $this->manager->getRepository(Sector::class)->count([]));
        self::assertSame(0, $this->manager->getRepository(Row::class)->count([]));
        self::assertSame(0, $this->manager->getRepository(Seat::class)->count([]));
    }
}
