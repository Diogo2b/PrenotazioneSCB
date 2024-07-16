<?php

namespace App\Test\Controller;

use App\Entity\Tribune;
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

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
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
        $fixture = new Tribune();
        $fixture->setName('Value');
        $fixture->setSigle('VL');
        $fixture->setNumberedSeats(false);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/tribune/');
        self::assertSame(0, $this->repository->count([]));
    }
}
