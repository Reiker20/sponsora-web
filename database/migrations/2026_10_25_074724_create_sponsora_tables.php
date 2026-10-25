<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'role')) {
                $t->string('role', 20)->default('sponsor')->after('password'); // organizer|sponsor|admin
            }
            if (!Schema::hasColumn('users', 'status_verifikasi')) {
                $t->string('status_verifikasi', 20)->default('Menunggu')->after('role'); // Menunggu|Disetujui|Ditolak
            }
            if (!Schema::hasColumn('users', 'catatan_verifikasi')) {
                $t->text('catatan_verifikasi')->nullable()->after('status_verifikasi');
            }
        });

        Schema::create('organizer_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('nama_organisasi');
            $t->string('jenis', 30); // HIMA/UKM, NGO, NPO, Komunitas
            $t->string('kontak', 30);
            $t->string('alamat')->nullable();
            $t->text('deskripsi')->nullable();
            $t->string('dokumen_legalitas')->nullable();
            $t->timestamps();
        });

        Schema::create('sponsor_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('nama_perusahaan');
            $t->string('nib', 30)->nullable();
            $t->string('fokus_csr')->nullable();
            $t->string('kontak', 30);
            $t->string('alamat')->nullable();
            $t->string('dokumen_legalitas')->nullable();
            $t->timestamps();
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('nama_kategori')->unique();
            $t->timestamps();
        });

        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organizer_id')->constrained('organizer_profiles')->cascadeOnDelete();
            $t->foreignId('category_id')->constrained('categories');
            $t->string('judul');
            $t->text('deskripsi');
            $t->string('lokasi');
            $t->string('penerima_manfaat')->nullable();
            $t->date('tanggal_pelaksanaan');
            $t->unsignedBigInteger('target_dana');
            $t->text('benefit_sponsor');
            $t->string('cover')->nullable();
            // Draft|Menunggu Verifikasi|Terverifikasi|Perlu Revisi|Selesai|Dilaporkan
            $t->string('status', 30)->default('Draft');
            $t->date('tanggal_selesai')->nullable();
            $t->date('batas_laporan')->nullable();
            $t->timestamps();
        });

        Schema::create('event_verifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained()->cascadeOnDelete();
            $t->foreignId('admin_id')->constrained('users');
            $t->string('status', 20); // Disetujui|Ditolak
            $t->text('catatan')->nullable();
            $t->timestamp('tanggal_verifikasi')->useCurrent();
            $t->timestamps();
        });

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

        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('pesan');
            $t->string('url')->nullable();
            $t->boolean('status_baca')->default(false);
            $t->timestamps();
        });

        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained()->cascadeOnDelete();
            $t->string('jenis', 20); // keuangan|hasil_program
            $t->text('ringkasan')->nullable();
            $t->text('capaian_hasil')->nullable();
            $t->text('kendala')->nullable();
            $t->string('berkas_dokumentasi')->nullable();
            $t->string('status', 20)->default('Draft'); // Draft|Dikirim|Perlu Revisi|Diterima
            $t->timestamp('tanggal_kirim')->nullable();
            $t->timestamps();
            $t->unique(['event_id', 'jenis']);
        });

        Schema::create('report_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('report_id')->constrained()->cascadeOnDelete();
            $t->string('uraian');
            $t->unsignedBigInteger('nominal');
            $t->string('bukti_berkas')->nullable();
            $t->timestamps();
        });

        Schema::create('report_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('report_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sponsor_id')->constrained('sponsor_profiles')->cascadeOnDelete();
            $t->string('status', 20); // Diterima|Perlu Revisi
            $t->text('catatan')->nullable();
            $t->timestamp('tanggal_tanggapan')->useCurrent();
            $t->timestamps();
            $t->unique(['report_id', 'sponsor_id']);
        });
    }

    public function down(): void
    {
        foreach (['report_reviews', 'report_items', 'reports', 'notifications', 'commitments', 'event_verifications',
                  'events', 'categories', 'sponsor_profiles', 'organizer_profiles'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'status_verifikasi', 'catatan_verifikasi']));
    }
};
