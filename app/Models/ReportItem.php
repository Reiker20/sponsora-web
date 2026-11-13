<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportItem extends Model
{
    protected $fillable = ['report_id', 'uraian', 'nominal', 'bukti_berkas'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function reviews()
    {
        return $this->hasMany(ReportItemReview::class);
    }

    public function reviewForSponsor(int $sponsorId): ?ReportItemReview
    {
        return $this->reviews->firstWhere('sponsor_id', $sponsorId)
            ?? $this->reviews()->where('sponsor_id', $sponsorId)->first();
    }

    public function buktiUrl(): ?string
    {
        if (! $this->bukti_berkas) {
            return null;
        }

        return route('files.show', ['jenis' => 'bukti', 'id' => $this->id]);
    }
}
