<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * All Institute materials use American English (the Institute's standing
 * rule, restated October 2026). Every screen and email the Portal shows is a
 * Blade template, so a British spelling arriving in any of them fails here
 * rather than waiting for somebody to notice it on the page.
 *
 * Only words whose British form never appears in code are listed, so the
 * check cannot be tripped by a column or class name.
 */
class AmericanEnglishTest extends TestCase
{
    private const BRITISH = [
        'programme', 'programmes', 'judgement', 'judgements',
        'recognise', 'recognised', 'recognises', 'recognisable', 'unrecognised',
        'organise', 'organised', 'organisational', 'organisations',
        'centre', 'centres', 'colour', 'colours', 'behaviour', 'favour', 'honour',
        'labour', 'neighbour', 'neighbours', 'ageing', 'licence', 'travelled',
        'cancelled', 'labelled', 'modelling', 'counselling', 'standardise',
        'standardised', 'summarise', 'apologise', 'realise', 'prioritise',
        'emphasise', 'authorise', 'catalogue', 'enquiry', 'whilst', 'amongst',
        'paediatric', 'orthopaedic', 'anaesthesia', 'fulfil',
    ];

    public function test_no_screen_or_email_uses_british_spelling(): void
    {
        $views = dirname(__DIR__, 2).'/resources/views';
        $pattern = '/\b('.implode('|', self::BRITISH).')\b/i';
        $found = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views));

        foreach ($files as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            foreach (file($file->getPathname()) ?: [] as $number => $line) {
                if (preg_match_all($pattern, $line, $matches)) {
                    foreach ($matches[1] as $word) {
                        $found[] = substr($file->getPathname(), strlen($views) + 1).':'.($number + 1).' '.$word;
                    }
                }
            }
        }

        $this->assertSame([], $found, "British spelling found:\n".implode("\n", $found));
    }
}
