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
        // Schema::create('commitments', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });
        Schema::create('commitments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sponsor_id')->constrained('sponsor_profiles')->cascadeOnDelete();
            $t->unsignedBigInteger('nominal');
            $t->string('bentuk_dukungan', 60);
            $t->text('catatan')->nullable();
            $t->text('catatan_organizer')->nullable();
            $t->unsignedBigInteger('nominal_usulan')->nullable();
            // Menunggu Tanggapan|Diterima|Ditolak|Negosiasi
            $t->string('status', 30)->default('Menunggu Tanggapan');
            $t->timestamp('tanggal_pengajuan')->useCurrent();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commitments');
    }
};
