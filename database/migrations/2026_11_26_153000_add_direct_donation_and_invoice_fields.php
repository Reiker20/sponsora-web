<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('nama_bank', 60)->nullable()->after('cover');
            $table->string('nomor_rekening', 40)->nullable()->after('nama_bank');
            $table->string('atas_nama_rekening', 100)->nullable()->after('nomor_rekening');
            $table->string('qris_image')->nullable()->after('atas_nama_rekening');
        });

        Schema::table('commitments', function (Blueprint $table) {
            $table->unsignedBigInteger('nominal_klaim')->nullable()->after('nominal');
            $table->unsignedBigInteger('nominal_tervalidasi')->nullable()->after('nominal_klaim');
            $table->string('nomor_invoice', 50)->nullable()->unique()->after('tanggal_bukti');
            $table->timestamp('tanggal_validasi')->nullable()->after('nomor_invoice');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nomor_rekening', 'atas_nama_rekening', 'qris_image']);
        });

        Schema::table('commitments', function (Blueprint $table) {
            $table->dropUnique(['nomor_invoice']);
            $table->dropColumn(['nominal_klaim', 'nominal_tervalidasi', 'nomor_invoice', 'tanggal_validasi']);
        });
    }
};
