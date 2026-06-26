<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\Newsletter\NewsletterUnsubscribeTokenService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @author Mathieu
 */
#[AsCommand(
    name: 'newsletter:unsubscribe:token',
    description: 'Generate or verify a newsletter unsubscribe token for a user.',
)]
final class NewsletterUnsubscribeTokenCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly NewsletterUnsubscribeTokenService $tokens,
        #[Autowire('%api_host%')]
        private readonly string $apiHost,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user-id', InputArgument::REQUIRED, 'User ID')
            ->addOption('token', null, InputOption::VALUE_REQUIRED, 'Token to verify against the user')
            ->addOption('scheme', null, InputOption::VALUE_REQUIRED, 'URL scheme (http or https)', 'https')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = (int) $input->getArgument('user-id');
        $user = $this->users->find($userId);
        if (null === $user) {
            $io->error(sprintf('User #%d not found.', $userId));

            return Command::FAILURE;
        }

        $email = (string) $user->getEmail();
        $expected = $this->tokens->generate($userId, $email);
        $scheme = (string) $input->getOption('scheme');
        $url = sprintf('%s://%s/newsletter/unsubscribe/%d/%s', $scheme, $this->apiHost, $userId, $expected);

        $io->writeln(sprintf('User #%d <%s>', $userId, $email));
        $io->writeln(sprintf('Expected token: %s', $expected));
        $io->writeln(sprintf('Unsubscribe URL: %s', $url));

        $token = $input->getOption('token');
        if (null !== $token) {
            if ($this->tokens->isValid($userId, $email, $token)) {
                $io->success('Provided token is valid.');
            } else {
                $io->error('Provided token is invalid.');
            }
        }

        return Command::SUCCESS;
    }
}
