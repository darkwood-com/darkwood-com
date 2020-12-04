<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(
        private CommonController $commonController
    )
    {
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: ['fr' => '/login', 'en' => '/en/login', 'de' => '/de/login'], name: 'security_login', defaults: ['ref' => 'home'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, ParameterBagInterface $parameterBag, $ref): Response
    {
        if ($request->getHost() === $parameterBag->get('admin_host')) {
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();
            return $this->render('admin/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        }
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('common/pages/login.html.twig', ['page' => $page, 'site_ref' => $siteRef, 'last_username' => $lastUsername, 'error' => $error]);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: ['fr' => '/logout', 'en' => '/en/logout', 'de' => '/de/logout'], name: 'security_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
