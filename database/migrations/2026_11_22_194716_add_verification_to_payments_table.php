<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('catatan_admin')->nullable()->after('status');
            $table->foreignId('verified_by')->nullable()->after('catatan_admin')->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_verifikasi')->nullable()->after('verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['catatan_admin', 'tanggal_verifikasi']);
        });
    }
};
