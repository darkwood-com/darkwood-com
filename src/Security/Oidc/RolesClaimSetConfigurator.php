<?php

declare(strict_types=1);

namespace App\Security\Oidc;

use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Entities\ClaimSetEntity;

/**
 * Registers a custom OIDC claim set so Symfony roles can be issued in ID tokens.
 *
 * @author Mathieu Ledru
 */
final class RolesClaimSetConfigurator
{
    public function __construct(
        private ClaimExtractor $claimExtractor,
    ) {
        if (!$this->claimExtractor->hasClaimSet('roles')) {
            $this->claimExtractor->addClaimSet(new ClaimSetEntity('roles', ['roles']));
        }
    }
}
