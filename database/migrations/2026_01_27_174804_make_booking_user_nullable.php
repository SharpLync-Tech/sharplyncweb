<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'bookings')
            ->where('CONSTRAINT_NAME', 'fk_bookings_user')
            ->exists();

        if ($fkExists) {
            DB::statement('ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_user`');
        }

        DB::statement('ALTER TABLE `bookings` MODIFY `user_id` BIGINT UNSIGNED NULL');

        DB::statement('ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE');
    }

    public function down(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'bookings')
            ->where('CONSTRAINT_NAME', 'fk_bookings_user')
            ->exists();

        if ($fkExists) {
            DB::statement('ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_user`');
        }

        DB::statement('ALTER TABLE `bookings` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE');
    }
};
