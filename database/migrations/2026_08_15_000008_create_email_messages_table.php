<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every outbound message is logged and its delivery status tracked via
 * Postmark webhooks. An invitation in a spam folder is an invitation that was
 * never sent — this table is what makes that failure visible and re-issuable
 * (doc 07 §3, risk #1 of the proposal).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_type');
            $table->string('to_email');
            $table->string('subject')->nullable();
            $table->string('postmark_message_id')->nullable()->unique();
            $table->string('status')->default('queued');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->text('error')->nullable();
            $table->timestampTz('queued_at');
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('bounced_at')->nullable();
            $table->timestampsTz();

            $table->index(['related_type', 'related_id']);
            $table->index('to_email');
            $table->index(['status', 'queued_at']);
        });

        DB::statement('ALTER TABLE email_messages ALTER COLUMN to_email TYPE citext');
        DB::statement("ALTER TABLE email_messages ADD CONSTRAINT email_messages_status_check
            CHECK (status IN ('queued', 'sent', 'delivered', 'bounced', 'spam_complaint', 'failed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('email_messages');
    }
};
