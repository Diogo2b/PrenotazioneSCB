<?php

namespace App\Test\Controller;

use App\Entity\Payment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private string $path = '/payment/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->clearDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearDatabase();
    }

    private function clearDatabase(): void
    {
        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE payment');
        $connection->executeStatement('TRUNCATE TABLE user');
        $connection->executeStatement('TRUNCATE TABLE ticket');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createUser(string $emailIdentifier): User
    {
        $user = new User();
        $user->setEmail('testuser' . uniqid($emailIdentifier, true) . '@example.com');
        $user->setRoles(['ROLE_USER']);
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
        self::assertPageTitleContains('Liste des Paiements');
        self::assertSelectorTextContains('h1', 'Liste des Paiements');
    }

    public function testNew(): void
    {
        $user = $this->createUser('new');

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'payment[amount]' => '20.50',
            'payment[status]' => true,
            'payment[user]' => $user->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->manager->getRepository(Payment::class)->count([]));
    }

    public function testShow(): void
    {
        $user = $this->createUser('show');

        $payment = new Payment();
        $payment->setAmount('20.50');
        $payment->setStatus(true);
        $payment->setUser($user);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($payment);
        $this->manager->flush();

        // Use the correct show URL
        $this->client->request('GET', sprintf('%s%s/show', $this->path, $payment->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Paiement');
        self::assertSelectorTextContains('body', '20.50');
    }

    public function testEdit(): void
    {
        $user = $this->createUser('edit');

        $payment = new Payment();
        $payment->setAmount('20.50');
        $payment->setStatus(true);
        $payment->setUser($user);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($payment);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $payment->getId()));

        $this->client->submitForm('Mettre à jour', [
            'payment[amount]' => '25.00',
            'payment[status]' => '0',  // Atualizando o valor para '0' ou '1'
            'payment[user]' => $user->getId(),
        ]);

        self::assertResponseRedirects($this->path);

        $updatedPayment = $this->manager->getRepository(Payment::class)->find($payment->getId());

        self::assertSame('25.00', number_format($updatedPayment->getAmount(), 2, '.', ''));
        self::assertFalse($updatedPayment->isStatus());
    }

    public function testRemove(): void
    {
        $user = $this->createUser('remove');

        $payment = new Payment();
        $payment->setAmount('20.50');
        $payment->setStatus(true);
        $payment->setUser($user);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($payment);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/show', $this->path, $payment->getId()));

        // Verificar o botão "Supprimer" e submetê-lo
        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->manager->getRepository(Payment::class)->count([]));
    }
}
