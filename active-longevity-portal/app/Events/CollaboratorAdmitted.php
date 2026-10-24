<?php

namespace App\Events;

use App\Models\Collaborator;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A person completed registration and became an admitted collaborator.
 * Future subscribers (welcome notification, Founder digest) attach here
 * without touching admission logic.
 */
class CollaboratorAdmitted
{
    use Dispatchable;

    public function __construct(
        public readonly Collaborator $collaborator,
    ) {}
}
