<?php

declare(strict_types=1);

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\User;

use App\Controller\CommonController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @todo use : bin/console make:reset-password
 * Controller managing the resetting of the password.
 */
#[Route('/', name: 'common_resetting')]
class ResettingController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController
    ) {}

    /**
     * Request reset user password: show form.
     */
    #[Route(path: ['fr' => '/fr/resetting/request', 'en' => '/resetting/request', 'de' => '/de/resetting/request'], name: '_request', defaults: ['ref' => 'resetting'])]
    public function request(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        return $this->render('common/pages/resettingRequest.html.twig', ['page' => $page, 'site_ref' => $siteRef]);
    }

    /**
     * Request reset user password: submit form and send email.
     */
    #[Route(path: ['fr' => '/fr/resetting/send-email', 'en' => '/resetting/send-email', 'de' => '/de/resetting/send-email'], name: '_send_email', defaults: ['ref' => 'resetting'])]
    public function sendEmail(Request $request, $ref): RedirectResponse
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        $username = $request->request->get('username');

        return new RedirectResponse($this->generateUrl('common_resetting_check_email', ['email' => 'test@gmail.com']));
    }
}
