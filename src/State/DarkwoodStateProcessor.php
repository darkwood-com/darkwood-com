<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Services\DarkwoodMcpForwarder;

final readonly class DarkwoodStateProcessor implements ProcessorInterface
{
    public function __construct(
        private DarkwoodMcpForwarder $forwarder,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        return $this->forwarder->forward('api_darkwood_get_state', 'GET');
    }
}
