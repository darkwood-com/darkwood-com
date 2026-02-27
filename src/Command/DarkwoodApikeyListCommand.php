<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ApiKeyRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'darkwood:apikey:list',
    description: 'List Darkwood API keys.',
)]
final class DarkwoodApikeyListCommand extends Command
{
    public function __construct(
        private readonly ApiKeyRepository $apiKeyRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $keys = $this->apiKeyRepository->findBy([], ['id' => 'ASC']);

        if ($keys === []) {
            $io->writeln('No API keys found.');

            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Active', 'Beta', 'Premium', 'Daily limit', 'Created']);
        foreach ($keys as $key) {
            $table->addRow([
                $key->getId(),
                $key->getName() ?? '-',
                $key->isActive() ? 'yes' : 'no',
                $key->isBeta() ? 'yes' : 'no',
                $key->isPremium() ? 'yes' : 'no',
                $key->getDailyActionLimit() ?? '-',
                $key->getCreated()?->format('Y-m-d H:i') ?? '-',
            ]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
