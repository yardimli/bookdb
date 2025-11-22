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
			Schema::create('book_caches', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('goodreads_id')->unique();
				$table->longText('data'); // Use longText for large JSON payloads containing reviews
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('book_caches');
		}
	};
