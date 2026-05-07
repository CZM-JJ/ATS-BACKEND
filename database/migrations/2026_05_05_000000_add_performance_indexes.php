<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds indexes to improve query performance for frequently filtered/sorted columns.
     */
    public function up(): void
    {
        // Applicants table indexes
        Schema::table('applicants', function (Blueprint $table) {
            // Unique index on email to prevent duplicates and speed up lookups
            if (!Schema::hasColumn('applicants', 'email_address') || !$this->indexExists('applicants', 'email_address')) {
                $table->index('email_address');
            }

            // Index for status filtering (used in queries and dashboard)
            if ($this->indexExists('applicants', 'status')) {
                $table->index('status');
            }

            // Index for position grouping (used in analytics)
            if ($this->indexExists('applicants', 'position_applied_for')) {
                $table->index('position_applied_for');
            }

            // Index for created_at (used in date range filters and recent count queries)
            if ($this->indexExists('applicants', 'created_at')) {
                $table->index('created_at');
            }

            // Index for vacancy_source (used in filtering and analytics)
            if ($this->indexExists('applicants', 'vacancy_source')) {
                $table->index('vacancy_source');
            }
        });

        // Audit logs table indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            // Index for entity_id lookups (timeline queries)
            if ($this->indexExists('audit_logs', 'entity_id')) {
                $table->index('entity_id');
            }

            // Index for user_id (user activity lookup)
            if ($this->indexExists('audit_logs', 'user_id')) {
                $table->index('user_id');
            }

            // Index for created_at (timeline sorting)
            if ($this->indexExists('audit_logs', 'created_at')) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndexIfExists(['email_address']);
            $table->dropIndexIfExists(['status']);
            $table->dropIndexIfExists(['position_applied_for']);
            $table->dropIndexIfExists(['created_at']);
            $table->dropIndexIfExists(['vacancy_source']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndexIfExists(['entity_id']);
            $table->dropIndexIfExists(['user_id']);
            $table->dropIndexIfExists(['created_at']);
        });
    }

    /**
     * Check if a column exists in a table.
     */
    private function indexExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
};
