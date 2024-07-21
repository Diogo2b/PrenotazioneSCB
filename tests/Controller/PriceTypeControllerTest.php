<?php

namespace App\Test\Controller;

use App\Entity\PriceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PriceTypeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        // Limpa as tabelas em ordem que respeite as chaves estrangeiras
        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('DELETE FROM ticket');
        $connection->executeStatement('DELETE FROM sport_match');
        $connection->executeStatement('DELETE FROM price_type');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function tearDown(): void
    {
        // Limpa as tabelas em ordem que respeite as chaves estrangeiras
        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('DELETE FROM ticket');
        $connection->executeStatement('DELETE FROM sport_match');
        $connection->executeStatement('DELETE FROM price_type');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/price/type/');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Types de Prix');
        // Adicione outras asserções aqui se necessário
    }

    public function testNew(): void
    {
        $crawler = $this->client->request('GET', '/price/type/new');

        self::assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Enregistrer')->form([
            'price_type[name]' => 'Test PriceType',
            'price_type[price]' => 100, // Adicione o campo de preço
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/price/type/');

        $priceType = $this->manager->getRepository(PriceType::class)->findOneBy(['name' => 'Test PriceType']);
        self::assertNotNull($priceType);
    }

    public function testShow(): void
    {
        $fixture = new PriceType();
        $fixture->setName('My Title');
        $fixture->setPrice(100); // Adicione o preço
        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('/price/type/%d', $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Type de Prix');
        // Adicione outras asserções aqui se necessário
    }

    public function testEdit(): void
    {
        $fixture = new PriceType();
        $fixture->setName('My Title');
        $fixture->setPrice(100);
        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('/price/type/%d/edit', $fixture->getId()));

        $form = $crawler->selectButton('Mettre à jour')->form([
            'price_type[name]' => 'Something New',
            'price_type[price]' => 200,
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/price/type/');

        // Recarregar a entidade para garantir que está sendo gerenciada
        $this->manager->clear();
        $updatedPriceType = $this->manager->getRepository(PriceType::class)->find($fixture->getId());

        self::assertSame('Something New', $updatedPriceType->getName());
        self::assertSame(200.00, (float) $updatedPriceType->getPrice());
    }


    public function testRemove(): void
    {
        $fixture = new PriceType();
        $fixture->setName('My Title');
        $fixture->setPrice(100); // Adicione o preço
        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('/price/type/%d', $fixture->getId()));
        $form = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/price/type/');
        self::assertNull($this->manager->getRepository(PriceType::class)->find($fixture->getId()));
    }
}
