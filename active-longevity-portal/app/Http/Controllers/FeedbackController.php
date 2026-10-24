<?php

namespace App\Http\Controllers;

use App\Models\FeedbackNote;
use App\Services\Governance\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Somewhere for a collaborator to tell the Institute what they find.
 *
 * Needed before Alpha, whose eight participants are the most valuable readers
 * the Portal will ever have. Deliberately plain: one box, and the page they
 * came from recorded alongside, since the same complaint means different
 * things on different screens.
 */
class FeedbackController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function create(Request $request): View
    {
        return view('collaborator.feedback', [
            'page' => $request->query('from') ?: url()->previous(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:5000'],
            'page' => ['nullable', 'string', 'max:300'],
        ], [
            'message.required' => 'Please write something, however brief.',
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $note = FeedbackNote::query()->create([
            'user_id' => $user->id,
            'message' => trim($validated['message']),
            'page' => $validated['page'] ?? null,
        ]);

        $this->audit->record('feedback.received', $user, $note);

        return redirect()->route('home')
            ->with('status', 'Thank you. The Institute reads every one of these.');
    }
}
