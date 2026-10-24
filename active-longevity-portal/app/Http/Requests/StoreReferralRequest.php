<?php

namespace App\Http\Requests;

use App\Models\ReferralRelationship;
use App\Support\FieldLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * A collaborator recommending a professional to the Institute.
 *
 * The relationship question is required. That is deliberate and is exactly why
 * "Prefer not to specify" appears on the list: a referrer who chooses it has
 * told the Institute something real, namely that they considered the question
 * and chose discretion. A blank field would only say the form allowed silence.
 */
class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->collaborator !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'candidate_first_name' => ['required', 'string', 'max:'.FieldLimits::NAME],
            'candidate_last_name' => ['required', 'string', 'max:'.FieldLimits::NAME],
            'candidate_email' => ['required', 'string', 'email:rfc', 'max:255'],
            'candidate_organisation' => ['nullable', 'string', 'max:'.FieldLimits::ORGANISATION],
            'candidate_title' => ['nullable', 'string', 'max:'.FieldLimits::TITLE],
            'candidate_country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'candidate_expertise' => ['nullable', 'array', 'max:12'],
            'candidate_expertise.*' => ['integer', Rule::exists('expertise_areas', 'id')->where('active', true)],
            'reason' => ['required', 'string', 'min:20', 'max:5000'],

            'referral_relationship_id' => [
                'required',
                Rule::exists('referral_relationships', 'id')->where('active', true),
            ],
            'relationship_detail' => ['nullable', 'string', 'max:'.FieldLimits::ORGANISATION],
            'acquaintance_note' => ['nullable', 'string', 'max:'.FieldLimits::NOTE],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $relationship = ReferralRelationship::query()->find($this->input('referral_relationship_id'));

            if ($relationship?->requires_detail && blank($this->input('relationship_detail'))) {
                $validator->errors()->add(
                    'relationship_detail',
                    'Please say briefly how you know this person.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'referral_relationship_id.required' => 'Please choose how you know this person. "Prefer not to specify" is a perfectly good answer.',
            'reason.required' => 'Please tell the Institute why you are recommending them.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'candidate_first_name' => 'first name',
            'candidate_last_name' => 'family name',
            'candidate_email' => 'email address',
            'candidate_organisation' => 'organization',
            'candidate_title' => 'professional title',
            'candidate_country_code' => 'country',
            'candidate_expertise' => 'areas of expertise',
            'referral_relationship_id' => 'relationship',
            'relationship_detail' => 'relationship detail',
            'acquaintance_note' => 'note',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'candidate_country_code' => filled($this->input('candidate_country_code'))
                ? strtoupper((string) $this->input('candidate_country_code'))
                : null,
        ]);
    }
}
