<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorProfile extends Model
{
    protected $fillable = [
        'user_id', 
        'nama_perusahaan', 
        'nib', 
        'fokus_csr', 
        'kontak', 
        'alamat', 
        'dokumen_legalitas'
    ];

    public function user() { return $this->belongsTo(User::class); }
}