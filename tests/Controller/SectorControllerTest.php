<?php

namespace App\Test\Controller;

use App\Entity\Sector;
use App\Entity\Tribune;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SectorControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/sector/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Sector::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sector index');
    }

    public function testNew(): void
    {
        $tribune = new Tribune();
        $tribune->setName('Testing Tribune');
        $tribune->setSigle('TT');
        $tribune->setNumberedSeats(true);

        $this->manager->persist($tribune);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'sector[name]' => 'Testing',
            'sector[sigle]' => 'TS',
            'sector[numberedSeats]' => true,
            'sector[capacity]' => 100,
            'sector[availableForSale]' => true,
            'sector[tribune]' => $tribune->getId(),
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

        $this->manager->persist($tribune);

        $fixture = new Sector();
        $fixture->setName('My Title');
        $fixture->setSigle('MT');
        $fixture->setNumberedSeats(true);
        $fixture->setCapacity(200);
        $fixture->setAvailableForSale(true);
        $fixture->setTribune($tribune);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sector');

        $crawler = $this->client->getCrawler();
        self::assertGreaterThan(0, $crawler->filter('td:contains("My Title")')->count());
    }

    public function testEdit(): void
    {
        $tribune = new Tribune();
        $tribune->setName('Testing Tribune');
        $tribune->setSigle('TT');
        $tribune->setNumberedSeats(true);

        $this->manager->persist($tribune);

        $fixture = new Sector();
        $fixture->setName('Value');
        $fixture->setSigle('V');
        $fixture->setNumberedSeats(true);
        $fixture->setCapacity(150);
        $fixture->setAvailableForSale(true);
        $fixture->setTribune($tribune);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'sector[name]' => 'Something New',
            'sector[sigle]' => 'SN',
            'sector[numberedSeats]' => true,
            'sector[capacity]' => 300,
            'sector[availableForSale]' => false,
            'sector[tribune]' => $tribune->getId(),
        ]);

        self::assertResponseRedirects('/sector/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('SN', $fixture[0]->getSigle());
        self::assertSame(true, $fixture[0]->isNumberedSeats());
        self::assertSame(300, $fixture[0]->getCapacity());
        self::assertSame(false, $fixture[0]->isAvailableForSale());
        self::assertSame($tribune->getId(), $fixture[0]->getTribune()->getId());
    }

    public function testRemove(): void
    {
        $tribune = new Tribune();
        $tribune->setName('Testing Tribune');
        $tribune->setSigle('TT');
        $tribune->setNumberedSeats(true);

        $this->manager->persist($tribune);

        $fixture = new Sector();
        $fixture->setName('Value');
        $fixture->setSigle('V');
        $fixture->setNumberedSeats(true);
        $fixture->setCapacity(150);
        $fixture->setAvailableForSale(true);
        $fixture->setTribune($tribune);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/sector/');
        self::assertSame(0, $this->repository->count([]));
    }
}
