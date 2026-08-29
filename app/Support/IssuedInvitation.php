<?php

namespace App\Support;

use App\Models\Invitation;

/**
 * An invitation together with the one and only copy of its link.
 *
 * Only the hash of the token is stored, so this object is the single moment at
 * which the usable link exists. It is returned rather than stashed, shown once,
 * and then gone: if it is lost, the answer is to reissue, which is one click
 * and invalidates the old link.
 */
final readonly class IssuedInvitation
{
    public function __construct(
        public Invitation $invitation,
        public string $rawToken,
        public string $acceptanceUrl,
    ) {}
}
