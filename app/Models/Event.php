<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $fillable = [
        'organizer_id', 'category_id', 'judul', 'deskripsi', 'lokasi', 'penerima_manfaat', 'tanggal_pelaksanaan',
        'target_dana', 'benefit_sponsor', 'cover', 'status', 'tanggal_selesai', 'batas_laporan',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date', 'tanggal_selesai' => 'date', 'batas_laporan' => 'date', 'target_dana' => 'integer',
    ];

    public function organizer()     { return $this->belongsTo(OrganizerProfile::class, 'organizer_id'); }
    public function category()      { return $this->belongsTo(Category::class); }
    public function verifications() { return $this->hasMany(EventVerification::class)->latest(); }
    public function commitments()   { return $this->hasMany(Commitment::class); }
    public function reports()       { return $this->hasMany(Report::class); }

    public function scopeCatalog($q) { return $q->where('status', 'Terverifikasi'); }

    /** Tambahkan kolom dana_sum & mitra_count agar tidak N+1 */
    public function scopeWithFunding($q)
    {
        return $q->withSum(['commitments as dana_sum' => fn ($c) => $c->where('status', 'Diterima')], 'nominal')
                 ->withCount(['commitments as mitra_count' => fn ($c) => $c->where('status', 'Diterima')]);
    }

    public function getDanaTerkumpulAttribute(): int
    {
        return (int) ($this->attributes['dana_sum'] ?? $this->commitments()->where('status', 'Diterima')->sum('nominal'));
    }

    public function getMitraCountAttribute(): int
    {
        return (int) ($this->attributes['mitra_count'] ?? $this->commitments()->where('status', 'Diterima')->count());
    }

    public function getProgressAttribute(): int
    {
        return $this->target_dana > 0 ? min(100, (int) round($this->dana_terkumpul / $this->target_dana * 100)) : 0;
    }

    public function getSisaHariAttribute(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->tanggal_pelaksanaan, false));
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover ? Storage::disk('public')->url($this->cover) : null;
    }

    public function isEditable(): bool        { return in_array($this->status, ['Draft', 'Perlu Revisi'], true); }
    public function acceptsCommitments(): bool { return $this->status === 'Terverifikasi'; }
    public function isPublic(): bool          { return in_array($this->status, ['Terverifikasi', 'Selesai', 'Dilaporkan'], true); }

    public function acceptedSponsorIds()
    {
        return $this->commitments()->where('status', 'Diterima')->pluck('sponsor_id')->unique();
    }

    public function report(string $jenis): ?Report
    {
        return $this->reports->firstWhere('jenis', $jenis) ?? $this->reports()->where('jenis', $jenis)->first();
    }
}
