<?php

declare(strict_types=1);

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Routing\Annotation\Route;

class FacebookController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * Link to this controller to start the "connect" process.
     */
    #[Route('/connect/facebook', name: 'connect_facebook_start')]
    public function connect(ClientRegistry $clientRegistry)
    {
        // on Symfony 3.3 or lower, $clientRegistry = $this->get('knpu.oauth2.registry');
        // will redirect to Facebook!
        return $clientRegistry->getClient('facebook_main')->redirect(['public_profile', 'email'], []);
    }

    /**
     * After going to Facebook, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml.
     */
    #[Route('/connect/facebook/check', name: 'connect_facebook_check')]
    public function connectCheck(ClientRegistry $clientRegistry)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)
        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient $client */
        $client = $clientRegistry->getClient('facebook_main');

        try {
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\FacebookUser $user */
            $user = $client->fetchUser();
            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            exit;
            // ...
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException) {
            // something went wrong!
            // probably you should return the reason to the user
            exit;
        }
    }
}
