<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     */
    protected $connection = 'sharpfleet';

    public function up(): void
    {
        Schema::connection($this->connection)->table('users', function (Blueprint $table) {
            if (!Schema::connection($this->connection)->hasColumn('users', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
                $table->index('archived_at');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('users', function (Blueprint $table) {
            if (Schema::connection($this->connection)->hasColumn('users', 'archived_at')) {
                $table->dropIndex(['archived_at']);
                $table->dropColumn('archived_at');
            }
        });
    }
};
