<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(
    name: 'darkwood:apikey:toggle',
    description: 'Enable or disable an API key by id.',
)]
final class DarkwoodApikeyToggleCommand extends Command
{
    public function __construct(
        private readonly ApiKeyRepository $apiKeyRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'API key id')
            ->addOption('active', null, InputOption::VALUE_REQUIRED, '1 to activate, 0 to deactivate', '1')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = (int) $input->getArgument('id');
        $active = (int) $input->getOption('active') === 1;

        $apiKey = $this->apiKeyRepository->find($id);
        if ($apiKey === null) {
            $io->error(sprintf('API key with id "%d" not found.', $id));

            return Command::FAILURE;
        }

        $apiKey->setIsActive($active);
        $this->em->flush();

        $io->success(sprintf('API key #%d is now %s.', $id, $active ? 'active' : 'inactive'));

        return Command::SUCCESS;
    }
}
