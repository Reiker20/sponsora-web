<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Kesehatan', 'Lingkungan', 'Pendidikan', 'Sosial', 'Olahraga', 'Kebudayaan'] as $k) {
            Category::firstOrCreate(['nama_kategori' => $k]);
        }
    }
}