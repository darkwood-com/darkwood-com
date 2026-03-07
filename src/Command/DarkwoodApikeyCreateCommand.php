<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(
    name: 'darkwood:apikey:create',
    description: 'Create a new Darkwood API key (shown once).',
)]
final class DarkwoodApikeyCreateCommand extends Command
{
    private const KEY_BYTES = 32;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Label for the key (e.g. "tester-01")')
            ->addOption('beta', null, InputOption::VALUE_REQUIRED, 'Beta access (1 or 0)', '1')
            ->addOption('premium', null, InputOption::VALUE_REQUIRED, 'Premium flag (1 or 0)', '0')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Daily action limit (ignored for premium if null)', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getOption('name');
        $isBeta = (int) $input->getOption('beta') === 1;
        $isPremium = (int) $input->getOption('premium') === 1;

        $limitOption = $input->getOption('limit');
        $dailyLimit = $limitOption === null || $limitOption === ''
            ? null
            : (int) $limitOption;

        $rawKey = $this->generateKey();
        $keyHash = hash('sha256', $rawKey);

        $apiKey = new ApiKey();
        $apiKey->setKeyHash($keyHash);
        $apiKey->setName($name);
        $apiKey->setIsActive(true);
        $apiKey->setIsBeta($isBeta);
        $apiKey->setIsPremium($isPremium);
        $apiKey->setDailyActionLimit($dailyLimit);

        $this->em->persist($apiKey);
        $this->em->flush();

        $io->success('API key created.');
        $io->writeln('Store this key securely; it will not be shown again.');
        $io->writeln(sprintf('beta: %s', $isBeta ? 'yes' : 'no'));
        $io->writeln(sprintf('premium: %s', $isPremium ? 'yes' : 'no'));
        $io->writeln(sprintf('dailyActionLimit: %s', $dailyLimit === null ? 'null' : (string) $dailyLimit));
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
