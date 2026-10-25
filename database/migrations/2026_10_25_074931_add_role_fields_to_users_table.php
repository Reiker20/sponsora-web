
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('sponsor');
            }

            if (!Schema::hasColumn('users', 'status_verifikasi')) {
                $table->string('status_verifikasi', 20)
                    ->default('Menunggu');
            }

            if (!Schema::hasColumn('users', 'catatan_verifikasi')) {
                $table->text('catatan_verifikasi')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            foreach (
                ['role', 'status_verifikasi', 'catatan_verifikasi']
                as $column
            ) {
                if (Schema::hasColumn('users', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
