<?php

namespace App\Test\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/user/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(User::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Utilisateurs');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'user[email]' => 'testing@example.com',
            'user[password]' => 'TestingPassword123',
            'user[username]' => 'TestingUser',
            'user[firstName]' => 'TestingFirstName',
            'user[lastName]' => 'TestingLastName',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new User();
        $fixture->setEmail('MyTitle@example.com');
        $fixture->setRoles(['ROLE_USER']);
        $fixture->setPassword('MyTitlePassword');
        $fixture->setUsername('MyTitleUsername');
        $fixture->setFirstName('MyTitleFirstName');
        $fixture->setLastName('MyTitleLastName');
        $fixture->setCreatedAt(new \DateTimeImmutable('now'));
        $fixture->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Utilisateur');

        // Verificar que os dados estão sendo exibidos corretamente
        self::assertStringContainsString('MyTitle@example.com', $this->client->getResponse()->getContent());
        self::assertStringContainsString('MyTitleUsername', $this->client->getResponse()->getContent());
    }

    public function testEdit(): void
    {
        $fixture = new User();
        $fixture->setEmail('InitialValue@example.com');
        $fixture->setRoles(['ROLE_USER']);
        $fixture->setPassword('InitialPassword');
        $fixture->setUsername('InitialUsername');
        $fixture->setFirstName('InitialFirstName');
        $fixture->setLastName('InitialLastName');
        $fixture->setCreatedAt(new \DateTimeImmutable('now'));
        $fixture->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Mettre à jour', [
            'user[email]' => 'UpdatedValue@example.com',
            'user[password]' => 'UpdatedPassword',
            'user[username]' => 'UpdatedUsername',
            'user[firstName]' => 'UpdatedFirstName',
            'user[lastName]' => 'UpdatedLastName',
        ]);

        self::assertResponseRedirects($this->path);

        $updatedUser = $this->repository->find($fixture->getId());

        self::assertSame('UpdatedValue@example.com', $updatedUser->getEmail());
        self::assertSame('UpdatedPassword', $updatedUser->getPassword());
        self::assertSame('UpdatedUsername', $updatedUser->getUsername());
        self::assertSame('UpdatedFirstName', $updatedUser->getFirstName());
        self::assertSame('UpdatedLastName', $updatedUser->getLastName());
    }

    public function testRemove(): void
    {
        $fixture = new User();
        $fixture->setEmail('ToRemove@example.com');
        $fixture->setRoles(['ROLE_USER']);
        $fixture->setPassword('ToRemovePassword');
        $fixture->setUsername('ToRemoveUsername');
        $fixture->setFirstName('ToRemoveFirstName');
        $fixture->setLastName('ToRemoveLastName');
        $fixture->setCreatedAt(new \DateTimeImmutable('now'));
        $fixture->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->repository->count([]));
    }
}
