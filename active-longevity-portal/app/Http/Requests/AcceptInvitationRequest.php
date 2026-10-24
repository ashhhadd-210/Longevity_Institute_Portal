<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Invitation;
use App\Support\FieldLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The invited person completing their own registration.
 *
 * They confirm the name the Institute typed rather than being stuck with it:
 * the Founder is the authority on admission, and they are the authority on the
 * facts of their own life (agreed 25 August 2026).
 */
class AcceptInvitationRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:'.FieldLimits::NAME],

            // "How would you like the Portal to address you?" Never phrased as
            // "if different from your first name".
            'preferred_name' => ['nullable', 'string', 'max:'.FieldLimits::NAME],

            'middle_name' => ['nullable', 'string', 'max:'.FieldLimits::NAME],
            'last_name' => ['required', 'string', 'max:'.FieldLimits::NAME],

            // Held apart from the family name so that sorting keeps a person
            // beside their own namesake.
            'name_suffix' => ['nullable', 'string', 'max:'.FieldLimits::NAME_SUFFIX],

            // Required only where the invitation carried no address of its own,
            // which is the copy-link route.
            'email' => [
                $this->invitationHasEmail() ? 'nullable' : 'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => $this->passwordRules(),
            'privacy_consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'privacy_consent.accepted' => 'Please confirm you have read the privacy notice.',
            'email.unique' => 'An account already exists for that address. Please sign in instead.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'preferred_name' => 'preferred name',
            'middle_name' => 'middle name',
            'last_name' => 'family name',
            'email' => 'email address',
        ];
    }

    /**
     * Whether the invitation already carries an address, in which case the
     * person is not asked for one. A copy-link invitation carries none, because
     * the Institute never had one to put there.
     */
    private function invitationHasEmail(): bool
    {
        $token = (string) $this->route('token');

        return Invitation::query()
            ->where('token_hash', Invitation::hashToken($token))
            ->whereNotNull('email')
            ->exists();
    }
}
