<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportItemReview extends Model
{
    protected $fillable = [
        'report_item_id',
        'sponsor_id',
        'status',
        'catatan',
    ];

    public function item()
    {
        return $this->belongsTo(ReportItem::class, 'report_item_id');
    }

    public function sponsor()
    {
        return $this->belongsTo(SponsorProfile::class, 'sponsor_id');
    }
}
