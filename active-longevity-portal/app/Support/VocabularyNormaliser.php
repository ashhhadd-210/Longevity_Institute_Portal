<?php

namespace App\Support;

/**
 * Reduces a professional term to a form that can be compared.
 *
 * The Institute's Alias Register draws a distinction worth preserving: a
 * spelling or punctuation difference is not a different professional concept
 * and should not occupy a row in the vocabulary. Ageing and aging are the same
 * word. Genetics & Genomics and Genetics and Genomics are the same Field. So
 * those differences are handled here instead, and the Register stays a record
 * of meaning rather than a list of typing variants.
 *
 * The one rule that matters: this is applied to BOTH sides. The stored term and
 * the term somebody types are reduced identically before being compared.
 * Otherwise "ageing" finds "aging" but "aging" does not find "ageing", which is
 * the sort of asymmetry nobody notices until a collaborator complains.
 */
final class VocabularyNormaliser
{
    /**
     * British and American forms of the same word. Written as American to
     * British so there is a single direction of travel; which side we collapse
     * toward does not matter, only that both sides collapse the same way.
     *
     * @var array<string, string>
     */
    private const SPELLINGS = [
        'aging' => 'ageing',
        'behavior' => 'behaviour',
        'behavioral' => 'behavioural',
        'labor' => 'labour',
        'organization' => 'organisation',
        'organizational' => 'organisational',
        'centered' => 'centred',
        'center' => 'centre',
        'modeling' => 'modelling',
        'modeled' => 'modelled',
        'orthopedic' => 'orthopaedic',
        'counseling' => 'counselling',
        'counselor' => 'counsellor',
        'optimization' => 'optimisation',
        'specialization' => 'specialisation',
        'pediatric' => 'paediatric',
        'geriatric' => 'geriatric',
        'anesthesia' => 'anaesthesia',
        'hematology' => 'haematology',
        'pediatrics' => 'paediatrics',
    ];

    /**
     * The comparable form of a term.
     */
    public static function key(?string $term): string
    {
        $value = mb_strtolower(trim((string) $term));

        if ($value === '') {
            return '';
        }

        // An ampersand is the word "and" wearing a hat.
        $value = str_replace('&', ' and ', $value);

        // Hyphens, slashes and punctuation separate words rather than change
        // them: "Speech-Language Pathology" and "Speech Language Pathology".
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        $words = array_filter(explode(' ', $value), static fn (string $w): bool => $w !== '');

        $words = array_map(static function (string $word): string {
            $word = self::SPELLINGS[$word] ?? $word;

            return self::singular($word);
        }, $words);

        // "and" and "of" carry no distinguishing weight between professional
        // terms, and dropping them lets "Genetics and Genomics" match
        // "Genetics Genomics" without a separate alias for the difference.
        $words = array_filter($words, static fn (string $w): bool => ! in_array($w, ['and', 'of', 'the'], true));

        return implode(' ', $words);
    }

    /**
     * Conservative singularisation. Only where it is professionally safe:
     * "Family Sciences" and "Family Science" are the same thing, while
     * "Statistics" and "Statistic" are emphatically not, so words ending in a
     * double s and a short list of discipline names ending in -ics are left be.
     */
    private static function singular(string $word): string
    {
        if (mb_strlen($word) <= 3) {
            return $word;
        }

        // Disciplines whose name simply ends in s: statistics, economics,
        // ethics, genomics, informatics, physics, mathematics.
        if (str_ends_with($word, 'ics') || str_ends_with($word, 'ss') || str_ends_with($word, 'us')) {
            return $word;
        }

        if (str_ends_with($word, 'ies') && mb_strlen($word) > 4) {
            return mb_substr($word, 0, -3).'y';
        }

        if (str_ends_with($word, 'es') && ! str_ends_with($word, 'ses')) {
            return mb_substr($word, 0, -1);
        }

        if (str_ends_with($word, 's')) {
            return mb_substr($word, 0, -1);
        }

        return $word;
    }

    /**
     * The searchable form of a credential, which is deliberately both its
     * letters and its meaning. Somebody who holds a Master of Public Health but
     * does not think of themselves as an MPH should still find it by typing
     * "public health".
     */
    public static function credentialKey(string $acronym, string $meaning, ?string $jurisdiction = null): string
    {
        return trim(implode(' ', array_filter([
            self::key($acronym),
            self::key($meaning),
            self::key($jurisdiction),
        ])));
    }
}
