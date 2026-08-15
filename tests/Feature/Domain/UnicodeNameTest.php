<?php

namespace Tests\Feature\Domain;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A collaborator's name must never be simplified or anglicized because of a
 * technical limitation (client standard, doc 02 §5).
 */
class UnicodeNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_names_with_extended_latin_and_diacritics_survive_round_trip(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Søren',
            'middle_name' => 'Ó',
            'last_name' => 'Kierkegård-Ñíguez',
        ]);

        $fresh = User::query()->findOrFail($user->id);

        $this->assertSame('Søren', $fresh->first_name);
        $this->assertSame('Ó', $fresh->middle_name);
        $this->assertSame('Kierkegård-Ñíguez', $fresh->last_name);
        $this->assertSame('Søren Ó Kierkegård-Ñíguez', $fresh->fullName());
    }

    public function test_email_uniqueness_is_case_insensitive(): void
    {
        User::factory()->create(['email' => 'Person@Example.org']);

        $this->expectException(QueryException::class);

        User::factory()->create(['email' => 'person@example.org']);
    }
}
