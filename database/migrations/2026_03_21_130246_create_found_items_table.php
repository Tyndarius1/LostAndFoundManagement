<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('found_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->string('item_name');
            $table->text('description');
            $table->string('color')->nullable();
            $table->string('found_location');
            $table->date('date_found');
            $table->string('image_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('reference_code')->unique();
            $table->string('storage_location');
            $table->enum('status', ['available', 'under_review', 'claimed', 'archived'])->default('available');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('found_items');
    }
};
