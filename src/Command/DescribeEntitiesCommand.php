<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class DescribeEntitiesCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager; // Injection du EntityManager
    }

    protected function configure(): void // Add void
    {
        $this
            ->setName('app:describe-entities')
            ->setDescription('Describes all entities and their fields and relationships.')
            ->setHelp('This command allows you to see all entities and their fields and relationships within the application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if (empty($metadata)) {
            $output->writeln("No metadata found.");
            return Command::SUCCESS;
        }

        foreach ($metadata as $m) {
            /** @var \Doctrine\ORM\Mapping\ClassMetadata $m */
            $output->writeln("Processing entity metadata...");
            $output->writeln("Class Name: " . get_class($m));
            $output->writeln("Entity: " . $m->getName());
            $output->writeln("Fields:");
            foreach ($m->getFieldNames() as $field) {
                $fieldMapping = $m->getFieldMapping($field);
                $output->writeln("  Field: $field, Type: " . $fieldMapping['type']);
            }
            $output->writeln("Relationships:");
            foreach ($m->getAssociationMappings() as $fieldName => $relation) {
                $output->writeln("  Relation Field: $fieldName");
                $output->writeln("    Type: " . $this->getAssociationType($relation['type']));
                $output->writeln("    Target Entity: " . $relation['targetEntity']);
            }
        }

        return Command::SUCCESS;
    }

    private function getAssociationType(int $type): string
    {
        switch ($type) {
            case ClassMetadata::ONE_TO_ONE:
                return 'OneToOne';
            case ClassMetadata::ONE_TO_MANY:
                return 'OneToMany';
            case ClassMetadata::MANY_TO_ONE:
                return 'ManyToOne';
            case ClassMetadata::MANY_TO_MANY:
                return 'ManyToMany';
            default:
                return 'Unknown';
        }
    }
}
