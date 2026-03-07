<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(
    name: 'darkwood:apikey:rotate',
    description: 'Rotate a Darkwood API key by id (old key deactivated, new key shown once).',
)]
final class DarkwoodApikeyRotateCommand extends Command
{
    private const KEY_BYTES = 32;

    public function __construct(
        private readonly ApiKeyRepository $apiKeyRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'API key id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = (int) $input->getArgument('id');

        $oldKey = $this->apiKeyRepository->find($id);
        if ($oldKey === null) {
            $io->error(sprintf('API key with id "%d" not found.', $id));

            return Command::FAILURE;
        }

        $rawKey = $this->generateKey();
        $keyHash = hash('sha256', $rawKey);

        $newKey = new ApiKey();
        $newKey->setKeyHash($keyHash);
        $newKey->setName($oldKey->getName());
        $newKey->setIsActive(true);
        $newKey->setIsBeta($oldKey->isBeta());
        $newKey->setIsPremium($oldKey->isPremium());
        $newKey->setDailyActionLimit($oldKey->getDailyActionLimit());

        $oldKey->setIsActive(false);

        $this->em->persist($newKey);
        $this->em->flush();

        $io->success(sprintf('API key #%d rotated. Old key is now inactive.', $id));
        $io->writeln('Store this key securely; it will not be shown again.');
        $io->writeln(sprintf('beta: %s', $newKey->isBeta() ? 'yes' : 'no'));
        $io->writeln(sprintf('premium: %s', $newKey->isPremium() ? 'yes' : 'no'));
        $io->writeln(sprintf('dailyActionLimit: %s', $newKey->getDailyActionLimit() === null ? 'null' : (string) $newKey->getDailyActionLimit()));
        $io->writeln('');
        $io->writeln($rawKey);

        return Command::SUCCESS;
    }

    private function generateKey(): string
    {
        $bytes = random_bytes(self::KEY_BYTES);
        $base64 = base64_encode($bytes);

        return rtrim(strtr($base64, '+/', '-_'), '=');
    }
}
