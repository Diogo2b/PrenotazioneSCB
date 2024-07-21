<?php

namespace App\Test\Controller;

use App\Entity\Seat;
use App\Entity\Row;
use App\Entity\Tribune; // Add this line
use App\Entity\Sector; // Add this line
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
        self::assertPageTitleContains('Liste des Sièges');

        $this->assertSelectorTextContains('h1', 'Liste des Sièges');
        $this->assertSelectorTextContains('a.btn.btn-primary', 'Créer nouveau');
        $this->assertSelectorExists('table.table');
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

        $this->client->submitForm('Enregistrer', [
            'seat[seatNumber]' => 1,
            'seat[row]' => $row->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        // Crie um Tribune para associar ao Sector
        $tribune = new Tribune();
        $tribune->setName('Tribune 1');
        $tribune->setNumberedSeats(true); // Definindo numbered_seats
        $this->manager->persist($tribune);
        $this->manager->flush();

        // Crie um Sector para associar ao Row
        $sector = new Sector();
        $sector->setName('Sector 1');
        $sector->setCapacity(100);
        $sector->setNumberedSeats(true);
        $sector->setAvailableForSale(true);
        $sector->setTribune($tribune);
        $this->manager->persist($sector);
        $this->manager->flush();

        // Crie um Row para associar ao Seat
        $row = new Row();
        $row->setSigle('A');
        $row->setCapacity(20);
        $row->setSector($sector);
        $this->manager->persist($row);
        $this->manager->flush();

        $fixture = new Seat();
        $fixture->setSeatNumber(1);
        $fixture->setRow($row);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);

        // Ajuste esta linha com o título correto da página
        self::assertPageTitleContains('Détails du Siège');
    }









    public function testEdit(): void
    {
        // Criação de uma Row para associar ao Seat
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

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Modifier Siège');

        $this->client->submitForm('Mettre à jour', [
            'seat[seatNumber]' => 2,
            'seat[row]' => $row->getId(),
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
        $this->client->submitForm('Supprimer');

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
