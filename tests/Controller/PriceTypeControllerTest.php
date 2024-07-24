<?php

namespace App\Test\Controller;

use App\Entity\PriceType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PriceTypeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private string $path = '/admin/price/type/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('DELETE FROM ticket');
        $connection->executeStatement('DELETE FROM sport_match');
        $connection->executeStatement('DELETE FROM price_type');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $user = $this->createUser('admin');
        $this->client->loginUser($user);
    }

    protected function tearDown(): void
    {
        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('DELETE FROM ticket');
        $connection->executeStatement('DELETE FROM sport_match');
        $connection->executeStatement('DELETE FROM price_type');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        parent::tearDown();
    }

    private function createUser(string $emailIdentifier): User
    {
        $user = new User();
        $user->setEmail('testuser' . uniqid($emailIdentifier, true) . '@example.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword('testpassword');
        $user->setUsername('testusername' . uniqid($emailIdentifier, true));
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Types de Prix');
    }

    public function testNew(): void
    {
        $crawler = $this->client->request('GET', $this->path . 'new');

        self::assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Enregistrer')->form([
            'price_type[name]' => 'Test PriceType',
            'price_type[price]' => 100,
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects($this->path);

        $priceType = $this->manager->getRepository(PriceType::class)->findOneBy(['name' => 'Test PriceType']);
        self::assertNotNull($priceType);
    }

    public function testShow(): void
    {
        $fixture = new PriceType();
        $fixture->setName('My Title');
        $fixture->setPrice(100);
        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%d', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Type de Prix');
    }

    public function testEdit(): void
    {
        $fixture = new PriceType();
        $fixture->setName('My Title');
        $fixture->setPrice(100);
        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

        $form = $crawler->selectButton('Mettre à jour')->form([
            'price_type[name]' => 'Something New',
            'price_type[price]' => 200,
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects($this->path);


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

        $crawler = $this->client->request('GET', sprintf('%s%d', $this->path, $fixture->getId()));
        $form = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($form);

        self::assertResponseRedirects($this->path);
        self::assertNull($this->manager->getRepository(PriceType::class)->find($fixture->getId()));
    }
}
