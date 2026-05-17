<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shared_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('note_id')->constrained()->onDelete('cascade');

            // Các cột thông tin phục vụ tính năng chia sẻ
            $table->string('recipient_email');
            $table->string('permission', 20)->default('view'); // view hoặc edit
            $table->timestamp('shared_at')->nullable(); // Cột lưu mốc thời gian chia sẻ

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_notes');
    }
};
