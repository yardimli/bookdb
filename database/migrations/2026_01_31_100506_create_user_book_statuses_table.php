<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_book_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['unread', 'reading', 'read', 'dnf'])->default('unread');

            $table->unsignedInteger('progress_page')->nullable();
            $table->unsignedTinyInteger('progress_percent')->nullable(); // 0-100

            // 狀態時間
            $table->date('started_at')->nullable();    // reading 開始
            $table->date('finished_at')->nullable();   // read 完成
            $table->date('dnf_at')->nullable();        // dnf 日期

            $table->timestamps();
            $table->unique(['user_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_book_statuses');
    }
};
