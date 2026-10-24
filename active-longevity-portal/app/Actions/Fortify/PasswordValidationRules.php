<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Length over composition rules (doc 05 §5); checked against known-breached
     * passwords via the k-anonymity range API. No forced periodic rotation.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::min(12)->uncompromised(), 'confirmed'];
    }
}
