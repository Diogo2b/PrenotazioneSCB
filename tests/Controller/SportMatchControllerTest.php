<?php

namespace App\Test\Controller;

use App\Entity\SportMatch;
use App\Entity\PriceType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SportMatchControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private EntityRepository $priceTypeRepository;
    private string $path = '/admin/sport/match/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(SportMatch::class);
        $this->priceTypeRepository = $this->manager->getRepository(PriceType::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();

        // Cria e loga um usuário para os testes
        $user = $this->createUser('admin');
        $this->client->loginUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE sport_match');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
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
        self::assertPageTitleContains('Liste des Matchs');
    }

    public function testNew(): void
    {
        $priceType = new PriceType();
        $priceType->setName('Test Price Type');
        $priceType->setPrice(100);
        $this->manager->persist($priceType);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Enregistrer', [
            'sport_match[homeTeam]' => 'Home Team',
            'sport_match[awayTeam]' => 'Away Team',
            'sport_match[matchDate]' => '2024-07-20',
            'sport_match[priceType]' => $priceType->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $priceType = new PriceType();
        $priceType->setName('Test Price Type');
        $priceType->setPrice(100);
        $this->manager->persist($priceType);

        $sportMatch = new SportMatch();
        $sportMatch->setHomeTeam('Home Team');
        $sportMatch->setAwayTeam('Away Team');
        $sportMatch->setMatchDate(new \DateTime('2024-07-20'));
        $sportMatch->setCreatedAt(new \DateTimeImmutable());
        $sportMatch->setUpdatedAt(new \DateTimeImmutable());
        $sportMatch->setPriceType($priceType);
        $this->manager->persist($sportMatch);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $sportMatch->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Détails du Match');
        $crawler = $this->client->getCrawler();
        self::assertStringContainsString('HOME TEAM', strtoupper($crawler->filter('table')->text()));
    }

    public function testEdit(): void
    {
        $priceType = new PriceType();
        $priceType->setName('Test Price Type');
        $priceType->setPrice(100);
        $this->manager->persist($priceType);

        $sportMatch = new SportMatch();
        $sportMatch->setHomeTeam('Home Team');
        $sportMatch->setAwayTeam('Away Team');
        $sportMatch->setMatchDate(new \DateTime('2024-07-20'));
        $sportMatch->setCreatedAt(new \DateTimeImmutable());
        $sportMatch->setUpdatedAt(new \DateTimeImmutable());
        $sportMatch->setPriceType($priceType);
        $this->manager->persist($sportMatch);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $sportMatch->getId()));

        $this->client->submitForm('Mettre à jour', [
            'sport_match[homeTeam]' => 'Updated Home Team',
            'sport_match[awayTeam]' => 'Updated Away Team',
            'sport_match[matchDate]' => '2024-08-20',
            'sport_match[priceType]' => $priceType->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        $updatedSportMatch = $this->repository->findAll()[0];
        self::assertSame('UPDATED HOME TEAM', strtoupper($updatedSportMatch->getHomeTeam()));
        self::assertSame('UPDATED AWAY TEAM', strtoupper($updatedSportMatch->getAwayTeam()));
        self::assertSame('2024-08-20', $updatedSportMatch->getMatchDate()->format('Y-m-d'));
    }

    public function testRemove(): void
    {
        $priceType = new PriceType();
        $priceType->setName('Test Price Type');
        $priceType->setPrice(100);
        $this->manager->persist($priceType);

        $sportMatch = new SportMatch();
        $sportMatch->setHomeTeam('Home Team');
        $sportMatch->setAwayTeam('Away Team');
        $sportMatch->setMatchDate(new \DateTime('2024-07-20'));
        $sportMatch->setCreatedAt(new \DateTimeImmutable());
        $sportMatch->setUpdatedAt(new \DateTimeImmutable());
        $sportMatch->setPriceType($priceType);
        $this->manager->persist($sportMatch);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $sportMatch->getId()));
        $this->client->submitForm('Supprimer');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->repository->count([]));
    }
}
