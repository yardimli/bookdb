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
			Schema::create('search_caches', function (Blueprint $table) {
				$table->id();
				$table->string('query');
				$table->integer('page')->default(1);
				$table->json('results'); // Stores the API response array
				$table->timestamps();

				// Index for fast lookups
				$table->index(['query', 'page']);
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('search_caches');
		}
	};
