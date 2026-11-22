<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'commitment_id', 'bank_account_id', 'rekening_tujuan', 'nama_pengirim', 'nominal_transfer',
        'tanggal_transfer', 'bukti_transfer', 'status',
    ];

    protected $casts = ['tanggal_transfer' => 'date', 'nominal_transfer' => 'integer'];

    public function commitment()  { return $this->belongsTo(Commitment::class); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }

    public function isPending(): bool  { return $this->status === 'Menunggu Verifikasi'; }
    public function isVerified(): bool { return $this->status === 'Terverifikasi'; }

    public function buktiUrl(): string
    {
        return route('files.show', ['jenis' => 'transfer', 'id' => $this->id]);
    }
}
