<?php

namespace App\Test\Controller;

use App\Entity\Seat;
use App\Entity\Row;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SeatControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/seat/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Seat::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Seat index');
    }

    public function testNew(): void
    {
        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $this->manager->persist($row);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'seat[seatNumber]' => 1,
            'seat[row]' => $row->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->repository->count([]));
    }


    public function testShow(): void
    {
        // Crie um Row para associar ao Seat
        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $this->manager->persist($row);
        $this->manager->flush();

        $fixture = new Seat();
        $fixture->setSeatNumber(1);
        $fixture->setRow($row);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Seat');
    }

    public function testEdit(): void
    {
        // Creation d'une rangéé pour associer au  Seat(siége)
        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $this->manager->persist($row);
        $this->manager->flush();

        $fixture = new Seat();
        $fixture->setSeatNumber(1);
        $fixture->setRow($row);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'seat[seatNumber]' => 2,
            'seat[row]' => $row->getId(), //  ID de Row cré
        ]);

        self::assertResponseRedirects($this->path);

        $fixture = $this->repository->findAll();
        self::assertSame(2, $fixture[0]->getSeatNumber());
    }

    public function testRemove(): void
    {
        // Garantir que o banco de dados está limpo
        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();

        $rowRepository = $this->manager->getRepository(Row::class);
        foreach ($rowRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();

        // Criar a Row e associar Seats
        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $this->manager->persist($row);
        $this->manager->flush();

        $seat1 = new Seat();
        $seat1->setSeatNumber(1);
        $seat1->setRow($row);
        $this->manager->persist($seat1);

        $seat2 = new Seat();
        $seat2->setSeatNumber(2);
        $seat2->setRow($row);
        $this->manager->persist($seat2);

        $this->manager->flush();

        // Verificar que há 2 Seats inicialmente
        self::assertSame(2, $this->repository->count([]));

        // Remover um Seat
        $this->client->request('GET', sprintf('%s%s', $this->path, $seat1->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects($this->path);

        // Verificar que apenas um Seat foi removido
        self::assertSame(1, $this->repository->count([]));

        // Verificar que a Row ainda existe
        self::assertSame(1, $rowRepository->count([]));

        // Verificar que o Seat restante está corretamente associado à Row
        $remainingSeat = $this->repository->findAll()[0];
        self::assertSame(2, $remainingSeat->getSeatNumber());
        self::assertSame($row->getId(), $remainingSeat->getRow()->getId());

        // Verificar que não há mais Seats extras
        $seats = $this->repository->findAll();
        self::assertCount(1, $seats);
    }
}
