<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\DarkwoodActionInput;
use App\Services\DarkwoodMcpForwarder;

final readonly class DarkwoodActionProcessor implements ProcessorInterface
{
    public function __construct(
        private DarkwoodMcpForwarder $forwarder,
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
