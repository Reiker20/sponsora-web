<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commitment extends Model
{
    protected $fillable = [
        'event_id', 'sponsor_id', 'nominal', 'bentuk_dukungan', 'catatan', 'catatan_organizer',
        'nominal_usulan', 'status', 'tanggal_pengajuan',
    ];
    protected $casts = ['tanggal_pengajuan' => 'datetime'];

    public function event()   { return $this->belongsTo(Event::class); }
    public function sponsor() { return $this->belongsTo(SponsorProfile::class, 'sponsor_id'); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function latestPayment() { return $this->hasOne(Payment::class)->latestOfMany(); }

    /** Sponsor boleh mengunggah bukti bila komitmen sudah diterima dan belum ada pembayaran yang aktif. */
    public function canUploadProof(): bool
    {
        return $this->status === 'Diterima'
            && ! $this->payments()->whereIn('status', ['Menunggu Verifikasi', 'Terverifikasi'])->exists();
    }
}