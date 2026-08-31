<?php

namespace App\Support;

/**
 * The character limits committed to the Institute on 25 August 2026.
 *
 * They live here so that validation and the database schema quote the same
 * numbers. A form that accepted more than a column holds would fail in front of
 * a person filling it in, and a form that accepted less would silently impose a
 * limit nobody agreed to.
 *
 * Counted in characters, not bytes: a Lithuanian or Chinese name is allowed
 * exactly as much room as an English one.
 */
final class FieldLimits
{
    /** First, middle, family and preferred names. */
    public const NAME = 100;

    /**
     * Generational suffixes: Jr., Sr., II, III. Short by nature, so the name
     * allowance does not apply. A long value here would signal a misunderstood
     * field rather than a long name.
     */
    public const NAME_SUFFIX = 20;

    /** Organisation and institution names, and departments. */
    public const ORGANISATION = 200;

    public const DEPARTMENT = 200;

    /**
     * Professional titles. A named chair carrying a donor, a department and a
     * joint appointment genuinely passes 150 characters, and nobody should
     * abbreviate their real title to fit a form designed around "Professor".
     */
    public const TITLE = 300;

    /** The collaborator-entered label on a profile link. */
    public const LINK_LABEL = 50;

    /** Free text the referrer adds about how they know a candidate. */
    public const NOTE = 2000;
}
