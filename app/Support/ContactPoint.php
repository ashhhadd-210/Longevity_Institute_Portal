<?php

namespace App\Support;

/**
 * A person the Portal is permitted to write to about a case, and the capacity
 * in which they are being written to.
 *
 * This exists so that "who may be contacted" is a value the domain returns and
 * a test can assert on, rather than a decision each message template makes for
 * itself. On the referral pathway the answer is the referrer, never the
 * candidate, and that must be impossible to get wrong by accident.
 */
final readonly class ContactPoint
{
    public function __construct(
        public string $email,
        public ?string $salutationName,
        public Capacity $capacity,
    ) {}

    public function greeting(): string
    {
        return Salutation::greeting($this->salutationName);
    }
}
