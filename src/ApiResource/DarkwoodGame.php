<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/darkwood/state',
            controller: 'App\Controller\Api\DarkwoodGetStateController',
            read: false,
            name: 'api_darkwood_get_state',
        ),
        new Post(
            uriTemplate: '/darkwood/action',
            controller: 'App\Controller\Api\DarkwoodPostActionController',
            read: false,
            deserialize: false,
            name: 'api_darkwood_post_action',
        ),
        new Get(
            uriTemplate: '/darkwood/archives',
            controller: 'App\Controller\Api\DarkwoodArchivesController',
            read: false,
            name: 'api_darkwood_archives',
        ),
    ],
)]
final class DarkwoodGame
{
}

