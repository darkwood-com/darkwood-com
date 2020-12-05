<?php

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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * @todo use : bin/console make:reset-password
 * Controller managing the resetting of the password.
 */
#[Route('/', name: 'common_resetting')]
class ResettingController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(
        private CommonController $commonController
    )
    {
    }
    /**
     * Request reset user password: show form.
     */
    #[Route(path: ['fr' => '/resetting/request', 'en' => '/en/resetting/request', 'de' => '/de/resetting/request'], name: '_request', defaults: ['ref' => 'resetting'])]
    public function request(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        return $this->render('common/pages/resettingRequest.html.twig', ['page' => $page, 'site_ref' => $siteRef]);
    }
    /**
     * Request reset user password: submit form and send email.
     */
    #[Route(path: ['fr' => '/resetting/send-email', 'en' => '/en/resetting/send-email', 'de' => '/de/resetting/send-email'], name: '_send_email', defaults: ['ref' => 'resetting'])]
    public function sendEmail(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        $username = $request->request->get('username');
        return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('common_resetting_check_email', ['email' => 'test@gmail.com']));
    }
}
