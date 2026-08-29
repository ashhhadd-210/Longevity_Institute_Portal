<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How the referrer knows the person they are recommending (agreed with the
 * Institute, 27 August 2026).
 *
 * A controlled list rather than free text, so that the answers can be read at a
 * glance and counted later. It is a table and not an enum because the Institute
 * expects to refine the wording once real referrals show what people actually
 * want to say; adding an option must be an administrative act, not a release.
 *
 * The list is deliberately answered *directionally* for mentoring: "Mentor"
 * alone cannot say who mentored whom, and the two cases make for very different
 * introductions.
 *
 * Both this answer and the free-text note are visible to the Institute alone,
 * permanently. They are a referrer's private statement about a relationship,
 * made about someone who does not yet know they are being discussed. They
 * inform how the Founder words an introduction; they never write it, because
 * the stated relationship is only one side's account of it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 120);
            // "Other" is the only option that asks for anything further.
            $table->boolean('requires_detail')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();
        });

        Schema::table('referrals', function (Blueprint $table) {
            // Nullable in the database because rows predating this column
            // cannot be invented; required by the referral form from now on,
            // which is precisely why "Prefer not to specify" is on the list.
            // A blank would only tell us the form permitted silence.
            $table->foreignId('referral_relationship_id')->nullable()
                ->constrained('referral_relationships')->restrictOnDelete();

            $table->string('relationship_detail', 200)->nullable()
                ->comment('Free text, used only when the chosen relationship requires detail');

            $table->text('acquaintance_note')->nullable()
                ->comment('Optional: how the referrer knows this person. Institute-only, permanently');
        });

        // The legacy free-text column is left in place rather than dropped:
        // migrations against a deployed database are additive. Nothing reads or
        // writes it, and the referral form never populated it, because no
        // referral form existed before this milestone.
        DB::statement("COMMENT ON COLUMN referrals.relationship_to_referrer IS
            'Superseded 29 August 2026 by referral_relationship_id. Never populated; retained because migrations after deployment are additive.'");
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referral_relationship_id');
            $table->dropColumn(['relationship_detail', 'acquaintance_note']);
        });

        Schema::dropIfExists('referral_relationships');
    }
};
