<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

use function filter_var;
use function str_starts_with;
use function substr;

use const FILTER_FLAG_HOSTNAME;
use const FILTER_VALIDATE_DOMAIN;

final class HostRedirectController extends AbstractController
{
    public function __construct(
        #[Autowire('%www_host%')]
        private readonly string $wwwHost,
    ) {}

    #[Route(
        path: '/{path}',
        name: 'redirect_www_to_apex',
        requirements: ['path' => '.*'],
        defaults: ['path' => ''],
        host: '%www_host%',
        priority: 1000,
    )]
    public function wwwToApex(Request $request): RedirectResponse
    {
        if (
            $request->getHost() !== $this->wwwHost
            || !str_starts_with($this->wwwHost, 'www.')
        ) {
            throw new NotFoundHttpException();
        }

        $canonicalHost = substr($this->wwwHost, 4);
        if (false === filter_var($canonicalHost, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new NotFoundHttpException();
        }

        return new RedirectResponse(
            $request->getScheme() . '://' . $canonicalHost . $request->getRequestUri(),
            Response::HTTP_PERMANENTLY_REDIRECT,
        );
    }

    #[Route(path: '/', name: 'admin_entrypoint', host: '%admin_host%', priority: 1000)]
    public function adminEntrypoint(): RedirectResponse
    {
        $route = null === $this->getUser() ? 'security_login' : 'admin_home';

        return $this->redirectToRoute($route, ['_locale' => 'fr'], Response::HTTP_FOUND);
    }
}
