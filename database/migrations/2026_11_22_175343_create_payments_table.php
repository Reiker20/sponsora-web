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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commitment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('rekening_tujuan');                 // salinan teks rekening saat transfer (riwayat tetap utuh)
            $table->string('nama_pengirim', 100);
            $table->unsignedBigInteger('nominal_transfer');
            $table->date('tanggal_transfer');
            $table->string('bukti_transfer');                  // path di disk 'local' (privat)
            $table->string('status', 30)->default('Menunggu Verifikasi'); // Menunggu Verifikasi|Terverifikasi|Ditolak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
