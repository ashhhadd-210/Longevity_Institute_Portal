<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The copy-link invitation route (agreed with the Institute, 18 and 25 August
 * 2026).
 *
 * Many of these relationships begin on LinkedIn, where the Founder often has no
 * email address for the person at all. She creates the record from a name
 * alone, generates the link, and sends it herself with her own words. The
 * milestone-one schema required an email address on every invitation, which
 * would have made that impossible, so the address becomes optional and the
 * channel becomes explicit.
 *
 * Making it explicit rather than inferring it from a null address matters for
 * honesty: delivery can only be observed for email. A copy-link invitation
 * shows issued and accepted and nothing in between, because the Portal did not
 * carry it and must never imply a delivery it cannot see.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('delivery_channel', 20)->default('email')->after('email');

            // Held so the invitation can greet the person as they would wish
            // even before they have an account of their own to state it in.
            $table->string('preferred_name', 100)->nullable()->after('first_name');
            $table->string('middle_name', 100)->nullable()->after('preferred_name');

            // The invitation names the referrer where one exists, and carries
            // an optional sentence in the Founder's own words.
            $table->foreignId('referrer_collaborator_id')->nullable()
                ->constrained('collaborators')->restrictOnDelete();
            $table->text('personal_message')->nullable();

            // One person, one case, one invitation: the link that makes a
            // second invitation for an existing case refusable.
            $table->foreignId('candidate_case_id')->nullable()
                ->constrained('candidate_cases')->restrictOnDelete();
        });

        DB::statement('ALTER TABLE invitations ALTER COLUMN email DROP NOT NULL');

        DB::statement("ALTER TABLE invitations ADD CONSTRAINT invitations_delivery_channel_check
            CHECK (delivery_channel IN ('email', 'copy_link'))");

        // An email invitation without an address is not an invitation. The
        // database refuses it rather than trusting every future code path to.
        DB::statement("ALTER TABLE invitations ADD CONSTRAINT invitations_email_required_for_email_delivery_check
            CHECK (delivery_channel <> 'email' OR email IS NOT NULL)");

        // At most one invitation may be outstanding or accepted for a case.
        // Revoked and expired invitations are excluded, so reissuing stays a
        // single click, and the rule is enforced where it cannot be forgotten.
        DB::statement("CREATE UNIQUE INDEX invitations_one_live_per_case_idx
            ON invitations (candidate_case_id)
            WHERE candidate_case_id IS NOT NULL AND status IN ('issued', 'sent', 'accepted')");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS invitations_one_live_per_case_idx');
        DB::statement('ALTER TABLE invitations DROP CONSTRAINT invitations_email_required_for_email_delivery_check');
        DB::statement('ALTER TABLE invitations DROP CONSTRAINT invitations_delivery_channel_check');

        DB::table('invitations')->whereNull('email')->delete();
        DB::statement('ALTER TABLE invitations ALTER COLUMN email SET NOT NULL');

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('candidate_case_id');
            $table->dropConstrainedForeignId('referrer_collaborator_id');
            $table->dropColumn(['delivery_channel', 'preferred_name', 'middle_name', 'personal_message']);
        });
    }
};
