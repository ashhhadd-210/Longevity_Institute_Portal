<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Set Aside, the fourth review action (agreed with the Institute,
 * 25 August 2026).
 *
 * It is a silent internal pause, not a judgement and not a rejection. The case
 * remains in the queue where it can be seen, because a case that disappears
 * when paused is a case that gets forgotten. Nobody outside the Institute
 * learns that it happened, so no message of any kind follows from it.
 *
 * It is deliberately not an admission decision: writing one would put a
 * judgement into the permanent record that nobody actually made.
 */
return new class extends Migration
{
    /** @var list<string> */
    private const APPLICATION_STATUSES = [
        'submitted', 'under_review', 'awaiting_information', 'set_aside', 'approved', 'declined', 'withdrawn',
    ];

    /** @var list<string> */
    private const REFERRAL_STATUSES = [
        'submitted', 'under_review', 'awaiting_information', 'set_aside', 'approved', 'declined',
    ];

    public function up(): void
    {
        foreach (['applications', 'referrals'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                // Who paused it and why. The reason is for the Institute's own
                // memory: months later the queue must be able to say why a case
                // is still waiting, not merely that it is.
                $blueprint->timestampTz('set_aside_at')->nullable();
                $blueprint->text('set_aside_reason')->nullable();
                $blueprint->foreignId('set_aside_by')->nullable()
                    ->constrained('users')->restrictOnDelete();
            });
        }

        $this->replaceStatusCheck('applications', self::APPLICATION_STATUSES);
        $this->replaceStatusCheck('referrals', self::REFERRAL_STATUSES);
    }

    public function down(): void
    {
        DB::table('applications')->where('status', 'set_aside')->update(['status' => 'under_review']);
        DB::table('referrals')->where('status', 'set_aside')->update(['status' => 'under_review']);

        $this->replaceStatusCheck('applications', array_values(array_diff(self::APPLICATION_STATUSES, ['set_aside'])));
        $this->replaceStatusCheck('referrals', array_values(array_diff(self::REFERRAL_STATUSES, ['set_aside'])));

        foreach (['applications', 'referrals'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropConstrainedForeignId('set_aside_by');
                $blueprint->dropColumn(['set_aside_at', 'set_aside_reason']);
            });
        }
    }

    /**
     * @param  list<string>  $statuses
     */
    private function replaceStatusCheck(string $table, array $statuses): void
    {
        $values = implode(', ', array_map(static fn (string $s): string => "'".$s."'", $statuses));

        DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$table}_status_check");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_status_check CHECK (status IN ({$values}))");
    }
};
