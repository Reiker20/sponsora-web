<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizerProfile extends Model
{
    protected $fillable = ['user_id', 'nama_organisasi', 'jenis', 'kontak', 'alamat', 'deskripsi', 'dokumen_legalitas'];

    public function user()   { return $this->belongsTo(User::class); }
    public function events() { return $this->hasMany(Event::class, 'organizer_id'); }
}