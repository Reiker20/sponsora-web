<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['event_id', 'jenis', 'ringkasan', 'capaian_hasil', 'kendala', 'berkas_dokumentasi', 'status', 'tanggal_kirim'];
    protected $casts = ['tanggal_kirim' => 'datetime'];

    public function event()   { return $this->belongsTo(Event::class); }
    public function items()   { return $this->hasMany(ReportItem::class); }
    public function reviews() { return $this->hasMany(ReportReview::class); }

    public function isEditable(): bool { return in_array($this->status, ['Draft', 'Perlu Revisi'], true); }
    public function isSent(): bool     { return $this->status !== 'Draft'; }

    /** Status laporan mengikuti tanggapan seluruh sponsor yang mendanai event. */
    public function refreshStatusFromReviews(): void
    {
        $sponsorIds = $this->event->acceptedSponsorIds();
        $reviews = $this->reviews()->get();

        if ($reviews->contains('status', 'Perlu Revisi')) {
            $status = 'Perlu Revisi';
        } elseif ($sponsorIds->isNotEmpty() && $sponsorIds->every(fn ($id) => $reviews->where('sponsor_id', $id)->where('status', 'Diterima')->isNotEmpty())) {
            $status = 'Diterima';
        } else {
            $status = 'Dikirim';
        }
        $this->update(['status' => $status]);
    }
}
