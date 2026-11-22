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
        Schema::table('commitments', function (Blueprint $table) {
            //Belum Dibayar | Menunggu Verifikasi | Terverifikasi | Ditolak (dua terakhir dipakai scrum verifikasi dana)
            $table->string('status_pembayaran', 30)->default('Belum Dibayar')->after('status');
            $table->string('bukti_pembayaran')->nullable()->after('status_pembayaran');
            $table->text('catatan_pembayaran')->nullable()->after('bukti_pembayaran');
            $table->timestamp('tanggal_bukti')->nullable()->after('catatan_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commitments', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran', 'bukti_pembayaran', 'catatan_pembayaran', 'tanggal_bukti']);
        });
    }
};
