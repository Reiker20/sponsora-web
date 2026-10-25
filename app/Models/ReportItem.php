<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportItem extends Model
{
    protected $fillable = ['report_id', 'uraian', 'nominal', 'bukti_berkas'];

    public function report() { return $this->belongsTo(Report::class); }
}
