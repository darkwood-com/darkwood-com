<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\DarkwoodActionInput;
use App\Service\DarkwoodMcpForwarderService;

final readonly class DarkwoodActionProcessor implements ProcessorInterface
{
    public function __construct(
        private DarkwoodMcpForwarderService $forwarder,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $query = [];
        if ($data instanceof DarkwoodActionInput && $data->query !== null) {
            $query = $data->query;
        }

        return $this->forwarder->forward('api_darkwood_post_action', 'POST', ['query' => $query]);
    }
}
