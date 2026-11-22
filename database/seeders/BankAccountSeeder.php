<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        BankAccount::updateOrCreate(
            ['nomor_rekening' => '1234567890'],
            ['nama_bank' => 'BCA', 'atas_nama' => 'Sponsora Platform', 'catatan' => 'Rekening utama (data demo).', 'is_active' => true]
        );
        BankAccount::updateOrCreate(
            ['nomor_rekening' => '9876543210'],
            ['nama_bank' => 'Mandiri', 'atas_nama' => 'Sponsora Platform', 'catatan' => 'Rekening cadangan (data demo).', 'is_active' => true]
        );
    }
}