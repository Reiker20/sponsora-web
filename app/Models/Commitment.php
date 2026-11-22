<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commitment extends Model
{
    protected $fillable = [
        'event_id', 'sponsor_id', 'nominal', 'bentuk_dukungan', 'catatan', 'catatan_organizer',
        'nominal_usulan', 'status', 'tanggal_pengajuan',
        'status_pembayaran', 'bukti_pembayaran', 'catatan_pembayaran', 'tanggal_bukti',
    ];
    // protected $casts = ['tanggal_pengajuan' => 'datetime'];
    protected $casts = ['tanggal_pengajuan' => 'datetime', 'tanggal_bukti' => 'datetime'];

    public function event()   { return $this->belongsTo(Event::class); }
    public function sponsor() { return $this->belongsTo(SponsorProfile::class, 'sponsor_id'); }
}