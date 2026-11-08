<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventVerification extends Model
{
    protected $fillable = ['event_id', 'admin_id', 'status', 'catatan', 'tanggal_verifikasi'];
    protected $casts = ['tanggal_verifikasi' => 'datetime'];

    public function event() { return $this->belongsTo(Event::class); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
}