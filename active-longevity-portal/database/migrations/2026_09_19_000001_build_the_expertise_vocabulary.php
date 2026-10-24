<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The expertise vocabulary, as agreed with the Institute through September 2026.
 *
 * A note on names, because two of them are historical. `disciplines` holds what
 * the Institute calls **Fields**, and `expertise_areas` holds what it calls
 * **Specialties**. Both tables were created in Milestone 1 under the earlier
 * vocabulary and are left named as they are rather than renamed underneath a
 * delivered, paid milestone. Every screen says Field and Specialty.
 *
 * Four decisions shape this migration.
 *
 * A Specialty relates to MANY Fields, not one. Health Policy genuinely sits
 * under both Public Policy and Public Health, and forcing a choice would make
 * the Institute file people in cupboards they did not choose.
 *
 * Those relationships exist for SEARCH ONLY. They never assign a Field. A
 * journalist who selects Health Policy as a Specialty remains a journalist:
 * the Portal will surface them to somebody searching Public Health without ever
 * labelling them a public health professional. Hence two independent join
 * tables for what a collaborator actually claims.
 *
 * Nothing is ever a dead end. If no term fits, the person writes their own,
 * their profile saves, and they continue. That is what `vocabulary_proposals`
 * is for, and it serves Fields, Specialties and credentials alike, because all
 * three follow the identical lifecycle: search, select, request if missing,
 * Institute reviews and standardises, permanent.
 *
 * And a credential is a RECORD, never an acronym. MD is Doctor of Medicine in
 * most of the world and Managing Director in others; FRCP differs between
 * London, Edinburgh, Glasgow and Canada. If the acronym were the identifier
 * those could not coexist.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The short distinguishing line for Fields with close neighbours. Its
        // job is not to define the Field but to answer one question: is this
        // one mine? Only the ~29 crowded Fields will carry one.
        Schema::table('disciplines', function (Blueprint $table) {
            $table->string('descriptor', 300)->nullable()->after('name');
            $table->string('search_key', 255)->nullable()->after('slug');
        });

        Schema::table('expertise_areas', function (Blueprint $table) {
            $table->string('search_key', 255)->nullable()->after('slug');
        });

        // ---------------------------------------------------------------- //
        // Specialty to Field: many to many, for search and discovery only.
        // ---------------------------------------------------------------- //
        Schema::create('discipline_expertise_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expertise_area_id')->constrained()->cascadeOnDelete();
            $table->timestampsTz();

            $table->unique(['discipline_id', 'expertise_area_id'], 'discipline_specialty_unique');
            $table->index('expertise_area_id');
        });

        // Carry across whatever the single-Field groundwork already recorded,
        // so no existing relationship is lost on the way to many.
        DB::statement('
            INSERT INTO discipline_expertise_area (discipline_id, expertise_area_id, created_at, updated_at)
            SELECT discipline_id, id, now(), now()
            FROM expertise_areas
            WHERE discipline_id IS NOT NULL
        ');

        Schema::table('expertise_areas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discipline_id');
        });

        // ---------------------------------------------------------------- //
        // What a collaborator actually claims. Two independent choices.
        // ---------------------------------------------------------------- //
        Schema::create('profile_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discipline_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();

            $table->unique(['profile_id', 'discipline_id'], 'profile_field_unique');
        });

        Schema::create('profile_specialties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expertise_area_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();

            $table->unique(['profile_id', 'expertise_area_id'], 'profile_specialty_unique');
        });

        // ---------------------------------------------------------------- //
        // Aliases. They surface a controlled term; they never select it.
        // ---------------------------------------------------------------- //
        Schema::create('expertise_aliases', function (Blueprint $table) {
            $table->id();
            $table->string('term', 255);
            $table->string('search_key', 255);

            // Exactly one of these is set. An alias resolves to an existing
            // controlled record and must never create a duplicate one.
            $table->foreignId('discipline_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('expertise_area_id')->nullable()->constrained()->cascadeOnDelete();

            // Abbreviations that could mean several things are still useful,
            // but must rank below exact and true-alias matches so that nobody
            // is confidently sent to the wrong place.
            $table->boolean('ambiguous')->default(false);
            $table->boolean('active')->default(true);
            $table->timestampsTz();

            $table->index('search_key');
        });

        DB::statement('
            ALTER TABLE expertise_aliases
            ADD CONSTRAINT expertise_aliases_single_target_check
            CHECK (num_nonnulls(discipline_id, expertise_area_id) = 1)
        ');

        // ---------------------------------------------------------------- //
        // Credentials. A record, never an acronym.
        // ---------------------------------------------------------------- //
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->string('acronym', 40);
            $table->string('meaning', 300);
            $table->string('jurisdiction', 120)->nullable();
            $table->string('credential_type', 80)->nullable();
            $table->string('search_key', 500);

            $table->boolean('approved')->default(false);
            $table->foreignId('proposed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestampsTz();

            $table->index('search_key');
            $table->index(['approved', 'active']);
        });

        // The same letters may appear repeatedly with different meanings, but
        // not twice with the same meaning in the same place.
        DB::statement('
            CREATE UNIQUE INDEX credentials_distinct_meaning_idx
            ON credentials (lower(acronym), lower(meaning), coalesce(lower(jurisdiction), \'\'))
        ');

        Schema::create('collaborator_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credential_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();

            $table->unique(['profile_id', 'credential_id'], 'profile_credential_unique');
        });

        // Up to three shown after the name, as agreed. Enforced by the
        // database so no future screen can quietly allow a fourth.
        DB::statement("
            CREATE OR REPLACE FUNCTION enforce_three_credentials() RETURNS trigger AS $$
            BEGIN
                IF (SELECT count(*) FROM collaborator_credentials
                    WHERE profile_id = NEW.profile_id) > 3 THEN
                    RAISE EXCEPTION 'a profile may display at most three credentials';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE CONSTRAINT TRIGGER collaborator_credentials_max_three
            AFTER INSERT OR UPDATE ON collaborator_credentials
            DEFERRABLE INITIALLY DEFERRED
            FOR EACH ROW EXECUTE FUNCTION enforce_three_credentials()
        ');

        // ---------------------------------------------------------------- //
        // One proposal mechanism, three vocabularies. Nobody is ever blocked.
        // ---------------------------------------------------------------- //
        Schema::create('vocabulary_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);                 // field | specialty | credential
            $table->string('term', 300);
            $table->string('meaning', 300)->nullable(); // credentials carry one
            $table->string('jurisdiction', 120)->nullable();

            $table->foreignId('proposed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('status', 20)->default('pending'); // pending|approved|declined|mapped
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            // Where it ended up, if the Institute mapped or approved it.
            $table->foreignId('resolved_discipline_id')->nullable()->constrained('disciplines')->nullOnDelete();
            $table->foreignId('resolved_expertise_area_id')->nullable()->constrained('expertise_areas')->nullOnDelete();
            $table->foreignId('resolved_credential_id')->nullable()->constrained('credentials')->nullOnDelete();

            $table->timestampsTz();

            $table->index(['status', 'kind']);
            $table->index('proposed_by_user_id');
        });

        DB::statement("
            ALTER TABLE vocabulary_proposals
            ADD CONSTRAINT vocabulary_proposals_kind_check
            CHECK (kind IN ('field','specialty','credential'))
        ");
        DB::statement("
            ALTER TABLE vocabulary_proposals
            ADD CONSTRAINT vocabulary_proposals_status_check
            CHECK (status IN ('pending','approved','declined','mapped'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabulary_proposals');
        DB::statement('DROP TRIGGER IF EXISTS collaborator_credentials_max_three ON collaborator_credentials');
        DB::statement('DROP FUNCTION IF EXISTS enforce_three_credentials()');
        Schema::dropIfExists('collaborator_credentials');
        Schema::dropIfExists('credentials');
        Schema::dropIfExists('expertise_aliases');
        Schema::dropIfExists('profile_specialties');
        Schema::dropIfExists('profile_fields');

        Schema::table('expertise_areas', function (Blueprint $table) {
            $table->foreignId('discipline_id')->nullable()->constrained()->nullOnDelete();
        });
        DB::statement('
            UPDATE expertise_areas ea
            SET discipline_id = (
                SELECT dea.discipline_id FROM discipline_expertise_area dea
                WHERE dea.expertise_area_id = ea.id
                ORDER BY dea.id LIMIT 1
            )
        ');
        Schema::dropIfExists('discipline_expertise_area');

        Schema::table('expertise_areas', function (Blueprint $table) {
            $table->dropColumn('search_key');
        });
        Schema::table('disciplines', function (Blueprint $table) {
            $table->dropColumn(['descriptor', 'search_key']);
        });
    }
};
