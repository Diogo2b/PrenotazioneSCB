<?php

namespace App\Test\Controller;

use App\Entity\Sector;
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

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'sector[name]' => 'Testing',
            'sector[sigle]' => 'Testing',
            'sector[numberedSeats]' => 'Testing',
            'sector[capacity]' => 'Testing',
            'sector[availableForSale]' => 'Testing',
            'sector[tribune]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sector();
        $fixture->setName('My Title');
        $fixture->setSigle('My Title');
        $fixture->setNumberedSeats('My Title');
        $fixture->setCapacity('My Title');
        $fixture->setAvailableForSale('My Title');
        $fixture->setTribune('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sector');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sector();
        $fixture->setName('Value');
        $fixture->setSigle('Value');
        $fixture->setNumberedSeats('Value');
        $fixture->setCapacity('Value');
        $fixture->setAvailableForSale('Value');
        $fixture->setTribune('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'sector[name]' => 'Something New',
            'sector[sigle]' => 'Something New',
            'sector[numberedSeats]' => 'Something New',
            'sector[capacity]' => 'Something New',
            'sector[availableForSale]' => 'Something New',
            'sector[tribune]' => 'Something New',
        ]);

        self::assertResponseRedirects('/sector/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getSigle());
        self::assertSame('Something New', $fixture[0]->getNumberedSeats());
        self::assertSame('Something New', $fixture[0]->getCapacity());
        self::assertSame('Something New', $fixture[0]->getAvailableForSale());
        self::assertSame('Something New', $fixture[0]->getTribune());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sector();
        $fixture->setName('Value');
        $fixture->setSigle('Value');
        $fixture->setNumberedSeats('Value');
        $fixture->setCapacity('Value');
        $fixture->setAvailableForSale('Value');
        $fixture->setTribune('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/sector/');
        self::assertSame(0, $this->repository->count([]));
    }
}
