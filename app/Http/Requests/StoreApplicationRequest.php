<?php

namespace App\Http\Requests;

use App\Support\FieldLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The public application to join the Institute.
 *
 * Deliberately short. Everything the Institute needs in order to decide whether
 * to talk to somebody is here; everything else the collaborator supplies later,
 * about their own life, in their own words.
 */
class StoreApplicationRequest extends FormRequest
{
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
            'middle_name' => ['nullable', 'string', 'max:'.FieldLimits::NAME],
            'last_name' => ['required', 'string', 'max:'.FieldLimits::NAME],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'organisation_text' => ['nullable', 'string', 'max:'.FieldLimits::ORGANISATION],
            'professional_title' => ['nullable', 'string', 'max:'.FieldLimits::TITLE],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'expertise_claimed' => ['nullable', 'array', 'max:12'],
            'expertise_claimed.*' => ['integer', Rule::exists('expertise_areas', 'id')->where('active', true)],
            'motivation' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motivation.required' => 'Please tell the Institute a little about why you would like to take part.',
            'motivation.min' => 'A sentence or two is enough, but please write something.',
            'professional_title.max' => 'Professional titles may be up to '.FieldLimits::TITLE.' characters.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'family name',
            'organisation_text' => 'organisation',
            'professional_title' => 'professional title',
            'country_code' => 'country',
            'expertise_claimed' => 'areas of expertise',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country_code' => filled($this->input('country_code'))
                ? strtoupper((string) $this->input('country_code'))
                : null,
        ]);
    }
}
