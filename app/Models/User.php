<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'status_verifikasi', 
        'catatan_verifikasi'
    ];
    protected $hidden = [
        'password', 
        'remember_token'
    ];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function organizerProfile() { return $this->hasOne(OrganizerProfile::class); }
    public function sponsorProfile()   { return $this->hasOne(SponsorProfile::class); }
    public function notifikasi()       { return $this->hasMany(Notifikasi::class)->latest(); }

    public function isAdmin(): bool     { return $this->role === 'admin'; }
    public function isOrganizer(): bool { return $this->role === 'organizer'; }
    public function isSponsor(): bool   { return $this->role === 'sponsor'; }
    public function isVerified(): bool  { return $this->status_verifikasi === 'Disetujui'; }

    public function profile() { return $this->isOrganizer() ? $this->organizerProfile : ($this->isSponsor() ? $this->sponsorProfile : null); }

    public function displayName(): string
    {
        return $this->profile()?->nama_organisasi ?? $this->profile()?->nama_perusahaan ?? $this->name;
    }
}
