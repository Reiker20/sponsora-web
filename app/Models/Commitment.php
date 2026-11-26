<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commitment extends Model
{
    protected $fillable = [
        'event_id', 'sponsor_id', 'nominal', 'nominal_klaim', 'nominal_tervalidasi',
        'bentuk_dukungan', 'catatan', 'catatan_organizer', 'nominal_usulan', 'status',
        'status_pembayaran', 'bukti_pembayaran', 'catatan_pembayaran', 'tanggal_bukti',
        'nomor_invoice', 'tanggal_validasi', 'tanggal_pengajuan',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'nominal_klaim' => 'integer',
        'nominal_tervalidasi' => 'integer',
        'tanggal_pengajuan' => 'datetime',
        'tanggal_bukti' => 'datetime',
        'tanggal_validasi' => 'datetime',
    ];

    public function event()         { return $this->belongsTo(Event::class); }
    public function sponsor()       { return $this->belongsTo(SponsorProfile::class, 'sponsor_id'); }
    public function payments()      { return $this->hasMany(Payment::class); }
    public function latestPayment() { return $this->hasOne(Payment::class)->latestOfMany(); }

    /** Sponsor boleh mengunggah bukti bila komitmen sudah diterima dan belum ada pembayaran yang aktif. */
    public function canUploadProof(): bool
    {
        return $this->status === 'Diterima'
            && ! $this->payments()->whereIn('status', ['Menunggu Verifikasi', 'Terverifikasi'])->exists();
    }

    public function hasInvoice(): bool
    {
        return $this->status_pembayaran === 'Terverifikasi' && filled($this->nomor_invoice);
    }

    public function hasNominalAdjustment(): bool
    {
        return $this->nominal_klaim !== null
            && $this->nominal_tervalidasi !== null
            && $this->nominal_klaim !== $this->nominal_tervalidasi;
    }
}
