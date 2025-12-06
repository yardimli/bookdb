<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Series;
use App\Models\Book;

return new class extends Migration {
    public function up(): void
    {
        $seriesList = DB::table('series')->get();

        foreach ($seriesList as $series) {
            if (!$series->books) continue;

            $books = json_decode($series->books, true);
            if (!is_array($books)) continue;

            foreach ($books as $index => $item) {

                // Insert into books table (or find if exists)
                $book = Book::firstOrCreate(
                    ['external_id' => $item['id']],
                    [
                        'title'  => $item['title'] ?? '',
                        'author' => $item['author'] ?? null,
                        'cover'  => $item['cover'] ?? null,
                        'rating' => $item['rating'] ?? null
                    ]
                );

                // Insert pivot relation with order
                DB::table('series_book')->insert([
                    'series_id'  => $series->id,
                    'book_id'    => $book->id,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Irreversible
    }
};
