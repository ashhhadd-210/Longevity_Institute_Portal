<?php

namespace App\Listeners;

use App\Events\CollaboratorAdmitted;
use App\Mail\CollaboratorRegisteredMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Lets the Founder know the moment an invited person registers (the
 * Institute's request, October 2026). Only the Founder is told; other
 * administrators see it on the Collaborators page.
 */
class NotifyInstituteOfRegistration implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(CollaboratorAdmitted $event): void
    {
        $founders = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'founder'))
            ->whereNotNull('email')
            ->get();

        foreach ($founders as $founder) {
            Mail::to($founder->email)->send(new CollaboratorRegisteredMail($event->collaborator));
        }
    }
}
