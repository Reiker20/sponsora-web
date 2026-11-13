<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_item_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_item_id')->constrained('report_items')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained('sponsor_profiles')->cascadeOnDelete();
            $table->string('status', 30)->default('Valid'); // Valid, Perlu Revisi, Kurang Bukti
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['report_item_id', 'sponsor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_item_reviews');
    }
};
