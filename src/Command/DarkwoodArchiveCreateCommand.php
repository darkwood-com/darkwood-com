<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\DarkwoodArchive;
use App\Repository\DarkwoodArchiveRepository;
use App\Services\GameService;
use BackedEnum;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Traversable;

use function is_array;
use function is_scalar;
use function sprintf;

#[AsCommand(
    name: 'darkwood:archive:create',
    description: 'Create a premium archive snapshot for a UTC date (no-op if already exists).',
)]
final class DarkwoodArchiveCreateCommand extends Command
{
    public function __construct(
        private readonly GameService $gameService,
        private readonly DarkwoodArchiveRepository $archiveRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('date', null, InputOption::VALUE_REQUIRED, 'UTC date YYYY-MM-DD', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dateOption = $input->getOption('date');
        $archiveDate = $dateOption !== null
            ? DateTimeImmutable::createFromFormat('Y-m-d', $dateOption)
            : new DateTimeImmutable('now', new DateTimeZone('UTC'));

        if ($archiveDate === false) {
            $io->error('Invalid --date; use YYYY-MM-DD.');

            return self::FAILURE;
        }

        $archiveDate = $archiveDate->setTime(0, 0, 0);
        $dateId = $archiveDate->format('Y-m-d');

        if ($this->archiveRepository->findOneByDateId($dateId) !== null) {
            $io->note(sprintf('Archive for %s already exists, skipping.', $dateId));

            return self::SUCCESS;
        }

        $request = new Request();
        $result = $this->gameService->play($request, null, null);

        if ($result instanceof Response) {
            $io->error('State endpoint returned a redirect/response; cannot snapshot.');

            return self::FAILURE;
        }

        $payload = $this->normalizeResult($result);

        $archive = new DarkwoodArchive();
        $archive->setArchiveDate($archiveDate);
        $archive->setPayload($payload);
        $archive->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('UTC')));

        $this->em->persist($archive);
        $this->em->flush();

        $io->success(sprintf('Created archive for %s.', $dateId));

        return self::SUCCESS;
    }

    private function normalizeResult(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeResult($item);
            }

            return $normalized;
        }

        if ($value instanceof Traversable) {
            $normalized = [];
            foreach ($value as $item) {
                $normalized[] = $this->normalizeResult($item);
            }

            return $normalized;
        }

        if (method_exists($value, 'getId')) {
            return $value->getId();
        }

        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }
}
