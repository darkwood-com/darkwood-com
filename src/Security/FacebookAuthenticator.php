<?php

namespace App\Security;

use App\Entity\User;
use App\Services\SiteService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FacebookAuthenticator extends \KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $em,
        private RouterInterface $router,
        /*
         * @var UrlGeneratorInterface
         */
        private UrlGeneratorInterface $urlGenerator,
        private ParameterBagInterface $parameterBag,
        /*
         * @var SiteService
         */
        private SiteService $siteService
    ) {
    }

    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_facebook_check';
    }

    public function getCredentials(Request $request)
    {
        // this method is only called if supports() returns true
        // For Symfony lower than 3.4 the supports method need to be called manually here:
        // if (!$this->supports($request)) {
        //     return null;
        // }
        return $this->fetchAccessToken($this->getFacebookClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var FacebookUser $facebookUser */
        $facebookUser = $this->getFacebookClient()->fetchUserFromToken($credentials);
        $email        = $facebookUser->getEmail();
        // 1) have they logged in with Facebook before? Easy!
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['facebookId' => $facebookUser->getId()]);
        if ($existingUser !== null) {
            return $existingUser;
        }
        $username = $facebookUser->getName();
        // 2) find or create new user
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $facebookUser->getEmail()]);
        if ($user === null) {
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setFirstname($facebookUser->getFirstName());
            $user->setLastname($facebookUser->getLastName());
        }
        $user->setFacebookId($facebookUser->getId());
        $imageUrl = $facebookUser->getPictureUrl();
        if ($imageUrl && !$user->getImageName()) {
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent) {
                $imageName = basename(preg_replace('/\?.*$/', '', $imageUrl));
                $tmpFile   = sys_get_temp_dir() . '/fb-' . $imageName;
                file_put_contents($tmpFile, $imageContent);
                $image = new UploadedFile($tmpFile, $imageName, null, null, true);
                $user->setImage($image);
            }
        }
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @return FacebookClient
     */
    private function getFacebookClient()
    {
        return $this->clientRegistry->getClient('facebook_main');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $redirectUrl = $request->headers->get('Referer');
        if (str_contains($redirectUrl, 'login')) {
            $redirectUrl = $this->urlGenerator->generate('darkwood_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $host        = $request->getHost();
            $site        = $this->siteService->findOneByHost($host);
            if ($host == $this->parameterBag->get('admin_host')) {
                $redirectUrl = $this->urlGenerator->generate('admin_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            } elseif ($site) {
                $redirectUrl = $this->urlGenerator->generate($site->getRef() . '_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        return new RedirectResponse($redirectUrl);
        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/',
            // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
