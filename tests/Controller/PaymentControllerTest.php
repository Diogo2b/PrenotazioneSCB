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
    private EntityManagerInterface $entityManager;
    private User $testUser;
    private string $path = '/payment/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Limpar dados antigos
        $this->cleanDatabase();

        // Criar usuário de teste
        $this->testUser = new User();
        $this->testUser->setEmail('testuser@example.com');
        $this->testUser->setRoles(['ROLE_USER']);
        $this->testUser->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $this->testUser->setUsername('TestUser');
        $this->testUser->setFirstName('Test');
        $this->testUser->setLastName('User');
        $this->testUser->setCreatedAt(new \DateTimeImmutable());
        $this->testUser->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($this->testUser);
        $this->entityManager->flush();
    }

    private function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->beginTransaction();
        try {
            $connection->executeStatement($platform->getTruncateTableSQL('payment', true /* whether to cascade */));
            $connection->executeStatement($platform->getTruncateTableSQL('user', true /* whether to cascade */));
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Paiements');
    }

    public function testNew(): void
    {
        $crawler = $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Enregistrer')->form([
            'payment[amount]' => '100.00',
            'payment[status]' => true,
            'payment[user]' => $this->testUser->getId(),
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->entityManager->getRepository(Payment::class)->count([]));
    }

    public function testShow(): void
    {
        $payment = new Payment();
        $payment->setAmount('50.00');
        $payment->setStatus(true);
        $payment->setUser($this->testUser);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $payment->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Paiement');

        self::assertStringContainsString('50.00', $this->client->getResponse()->getContent());
    }

    public function testEdit(): void
    {
        $payment = new Payment();
        $payment->setAmount('75.00');
        $payment->setStatus(true);
        $payment->setUser($this->testUser);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s/edit', $this->path, $payment->getId()));

        self::assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Mettre à jour')->form([
            'payment[amount]' => '80.00',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects($this->path);

        $updatedPayment = $this->entityManager->getRepository(Payment::class)->find($payment->getId());

        self::assertSame('80.00', $updatedPayment->getAmount());
    }

    public function testDelete(): void
    {
        $payment = new Payment();
        $payment->setAmount('25.00');
        $payment->setStatus(true);
        $payment->setUser($this->testUser);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $payment->getId()));

        self::assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($form);

        self::assertResponseRedirects($this->path);

        self::assertSame(0, $this->entityManager->getRepository(Payment::class)->count([]));
    }
}
