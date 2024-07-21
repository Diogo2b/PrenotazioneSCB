<?php

namespace App\Test\Controller;

use App\Entity\PriceType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PriceTypeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/price/type/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(PriceType::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Types de Prix');

        self::assertSelectorTextContains('h1', 'Liste des Types de Prix');
        self::assertSelectorExists('a.btn.btn-primary:contains("Créer nouveau")');
    }

    public function testNew(): void
    {
        $crawler = $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'price_type[name]' => 'Testing',
            'price_type[price]' => '99.99',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new PriceType();
        $fixture->setName('My Title');
        $fixture->setPrice('99.99');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Type de Prix');

        self::assertSelectorTextContains('h1', 'Type de Prix');

        // Verificar células específicas dentro das linhas da tabela
        $this->assertTableRowText($crawler, 1, 'Id', (string)$fixture->getId());
        $this->assertTableRowText($crawler, 2, 'Nom', 'My Title');
        $this->assertTableRowText($crawler, 3, 'Prix', '99.99');
    }

    private function assertTableRowText($crawler, int $row, string $header, string $value)
    {
        $headerSelector = sprintf('table.table > tbody > tr:nth-child(%d) > th', $row);
        $valueSelector = sprintf('table.table > tbody > tr:nth-child(%d) > td', $row);

        self::assertSelectorTextContains($headerSelector, $header);
        self::assertSelectorTextContains($valueSelector, $value);
    }

    public function testEdit(): void
    {
        $fixture = new PriceType();
        $fixture->setName('Value');
        $fixture->setPrice('99.99');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Mettre à jour', [
            'price_type[name]' => 'Something New',
            'price_type[price]' => '199.99',
        ]);

        self::assertResponseRedirects($this->path);

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('199.99', $fixture[0]->getPrice());
    }

    public function testRemove(): void
    {
        $fixture = new PriceType();
        $fixture->setName('Value');
        $fixture->setPrice('99.99');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->repository->count([]));
    }
}
