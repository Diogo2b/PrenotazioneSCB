<?php

namespace App\Test\Controller;

use App\Entity\Row;
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

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'row[sigle]' => 'Testing',
            'row[capacity]' => 'Testing',
            'row[sector]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Row();
        $fixture->setSigle('My Title');
        $fixture->setCapacity('My Title');
        $fixture->setSector('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Row');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Row();
        $fixture->setSigle('Value');
        $fixture->setCapacity('Value');
        $fixture->setSector('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'row[sigle]' => 'Something New',
            'row[capacity]' => 'Something New',
            'row[sector]' => 'Something New',
        ]);

        self::assertResponseRedirects('/row/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getSigle());
        self::assertSame('Something New', $fixture[0]->getCapacity());
        self::assertSame('Something New', $fixture[0]->getSector());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Row();
        $fixture->setSigle('Value');
        $fixture->setCapacity('Value');
        $fixture->setSector('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/row/');
        self::assertSame(0, $this->repository->count([]));
    }
}
