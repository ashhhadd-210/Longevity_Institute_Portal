<?php

namespace App\Http\Controllers;

use App\Services\Expertise\ExpertiseSearch;
use App\Support\ExpertiseMatch;
use App\Support\VocabularyNormaliser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * What the selector asks as somebody types.
 *
 * Deliberately thin. All the judgment lives in ExpertiseSearch, and this
 * returns exactly what the screen needs to draw a result: the controlled name,
 * whether it is a Field or a Specialty, why it appeared if that was not
 * obvious, and the Fields a Specialty is recognizable within.
 *
 * It offers. It never selects.
 */
class ExpertiseSearchController extends Controller
{
    public function __construct(private readonly ExpertiseSearch $search) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:120'],
            'only' => ['nullable', 'in:field,specialty'],
            'within' => ['nullable', 'integer'],
        ]);

        $term = (string) $validated['q'];
        $only = $validated['only'] ?? null;

        // The Field Register is explicit: Longevity is not a controlled Field,
        // and typing it must not produce a list of everything longevity-related.
        // It spans the whole Institute, so the selector says so and invites the
        // person to name the profession they bring to it instead.
        if (VocabularyNormaliser::key($term) === 'longevity') {
            return response()->json([
                'results' => [],
                'notice' => 'Longevity is what the whole Institute is about rather than a Field of its own. '
                    .'Please search for the profession or discipline you bring to it. If nothing fits, enter your own term below.',
            ]);
        }

        $results = match ($only) {
            'field' => $this->search->searchFields($term),
            'specialty' => $this->search->searchSpecialties(
                $term,
                isset($validated['within']) ? (int) $validated['within'] : null,
            ),
            default => $this->search->search($term),
        };

        return response()->json([
            'results' => $results->map(fn (ExpertiseMatch $m): array => [
                'kind' => $m->kind,
                'id' => $m->id,
                'name' => $m->name,
                'label' => $m->label(),
                'descriptor' => $m->descriptor,
                'context' => $m->context,
                'explanation' => $m->explanation(),
            ])->all(),
        ]);
    }
}
