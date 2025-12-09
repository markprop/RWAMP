<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that should receive a ULID column.
     *
     * Some of these (pages, posts, docs, projects, news, kyc_submissions)
     * may not exist in this project – we guard with Schema::hasTable().
     */
    protected array $tables = [
        'users',
        'crypto_payments',
        'withdraw_requests',
        'reseller_applications',
        'transactions',
        // Optional / future content tables
        'pages',
        'posts',
        'docs',
        'projects',
        'news',
        'kyc_submissions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (!Schema::hasTable($name)) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'ulid')) {
                    // Nullable for smooth rollout; will be backfilled later.
                    $table->char('ulid', 26)->nullable()->unique()->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            if (!Schema::hasTable($name) || !Schema::hasColumn($name, 'ulid')) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) {
                $table->dropUnique($table->getTable() . '_ulid_unique');
                $table->dropColumn('ulid');
            });
        }
    }
};


