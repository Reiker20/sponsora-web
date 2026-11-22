<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = ['nama_bank', 'nomor_rekening', 'atas_nama', 'catatan', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Teks ringkas, mis. "BCA 1234567890 a.n. Sponsora Platform" */
    public function label(): string
    {
        return "{$this->nama_bank} {$this->nomor_rekening} a.n. {$this->atas_nama}";
    }

}
