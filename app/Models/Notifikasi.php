<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifications';
    protected $fillable = ['user_id', 'pesan', 'url', 'status_baca'];
    protected $casts = ['status_baca' => 'boolean'];

    public static function kirim(?int $userId, string $pesan, ?string $url = null): void
    {
        if ($userId) {
            static::create(['user_id' => $userId, 'pesan' => $pesan, 'url' => $url]);
        }
    }

    public static function kirimAdmin(string $pesan, ?string $url = null): void
    {
        User::where('role', 'admin')->pluck('id')->each(fn ($id) => static::kirim($id, $pesan, $url));
    }
}