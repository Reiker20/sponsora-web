<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReview extends Model
{
    protected $fillable = ['report_id', 'sponsor_id', 'status', 'catatan', 'tanggal_tanggapan'];
    protected $casts = ['tanggal_tanggapan' => 'datetime'];

    public function report()  { return $this->belongsTo(Report::class); }
    public function sponsor() { return $this->belongsTo(SponsorProfile::class, 'sponsor_id'); }
}