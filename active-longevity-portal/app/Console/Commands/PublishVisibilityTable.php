<?php

namespace App\Console\Commands;

use App\Enums\Audience;
use App\Support\Visibility\FieldVisibility;
use App\Support\Visibility\VisibilityRegister;
use Illuminate\Console\Command;

/**
 * Writes the field-by-field visibility table the Institute approves.
 *
 * The point is that it is generated from the same declarations the software
 * obeys, rather than written alongside them. A table maintained by hand would
 * drift from the code within a month, and the drift is exactly what the
 * Institute was trying to prevent when it asked for this.
 *
 * So the document below is not a description of the rules. It is the rules,
 * printed.
 */
class PublishVisibilityTable extends Command
{
    protected $signature = 'portal:visibility-table {--path= : Where to write the document}';

    protected $description = 'Generate the field-by-field visibility table from the rules the Portal enforces';

    public function handle(): int
    {
        $path = (string) ($this->option('path') ?? base_path('docs/11-visibility-table.md'));

        $lines = [
            '# Who can see what',
            '',
            '*Generated from the rules the Portal enforces. Do not edit by hand:*',
            '*change the declarations and run `php artisan portal:visibility-table` again.*',
            '',
            'Four audiences, from most protected to least. A collaborator may always',
            'choose to show less than the Institute permits, and never more.',
            '',
        ];

        foreach (Audience::cases() as $audience) {
            $lines[] = sprintf('- **%s**', $audience->label());
        }

        $lines[] = '';
        $lines[] = 'Nothing is placed in the fourth. It exists so that if the Institute ever';
        $lines[] = 'decides something should be visible on the open web, that is a setting';
        $lines[] = 'rather than a rebuild.';
        $lines[] = '';

        foreach (Audience::cases() as $audience) {
            /** @var array<string, FieldVisibility> $inThisAudience */
            $inThisAudience = array_filter(
                VisibilityRegister::all(),
                static fn (FieldVisibility $f): bool => $f->ceiling === $audience,
            );

            $lines[] = '## '.$audience->label();
            $lines[] = '';

            if ($inThisAudience === []) {
                $lines[] = '*Nothing. Deliberately.*';
                $lines[] = '';

                continue;
            }

            $lines[] = '| Information | Who decides | Why this limit |';
            $lines[] = '|---|---|---|';

            foreach ($inThisAudience as $field) {
                $lines[] = sprintf('| %s | %s | %s |', $field->label, $field->decidedBy(), $field->because);
            }

            $lines[] = '';
        }

        $lines[] = '## What this guarantees';
        $lines[] = '';
        $lines[] = '- A field nobody has declared belongs to the Institute alone, and the';
        $lines[] = '  test suite refuses to pass until somebody states an intention. The';
        $lines[] = '  Portal\'s mistakes err toward privacy.';
        $lines[] = '- Every ceiling traces back to the promise made when the information was';
        $lines[] = '  collected. A field is never later shown more widely than the person was';
        $lines[] = '  told when they gave it.';
        $lines[] = '- Enforcement happens in one place rather than page by page, so no future';
        $lines[] = '  screen can get this wrong by forgetting.';
        $lines[] = '';

        file_put_contents($path, implode("\n", $lines)."\n");

        $this->components->info('Visibility table written to '.$path);
        $this->components->twoColumnDetail('Fields declared', (string) count(VisibilityRegister::all()));

        return self::SUCCESS;
    }
}
