<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\DarkwoodArchiveIdInput;
use App\Services\DarkwoodMcpForwarder;

final readonly class DarkwoodArchiveGetProcessor implements ProcessorInterface
{
    public function __construct(
        private DarkwoodMcpForwarder $forwarder,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $id = '';
        if ($data instanceof DarkwoodArchiveIdInput) {
            $id = $data->id;
        }

        return $this->forwarder->forward('api_darkwood_archive_get', 'GET', null, ['id' => $id]);
    }
}
