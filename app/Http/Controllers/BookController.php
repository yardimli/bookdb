<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use App\Models\Series;
	use App\Models\SearchCache;
	use App\Models\BookCache;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Str;

	class BookController extends Controller
	{
		/**
		 * Helper to upgrade Goodreads/Amazon low-res thumbnails to higher res.
		 */
		private function getHighResImage($url)
		{
			if (!$url) return 'https://placehold.co/300x450?text=No+Cover';

			// Pattern matches ._SX50_, ._SY75_, ._SX98_, etc.
			return preg_replace('/._S[XY]\d+_/', '._SY300_', $url);
		}

		public function index()
		{
			$series = Series::where('user_id', Auth::id())->get();
			return view('dashboard', compact('series'));
		}

		public function search(Request $request)
		{
			$query = $request->input('q');
			$page = $request->input('page', 1);
			$results = [];

			if ($query) {
				// 1. Check Cache
				$cached = SearchCache::where('query', $query)
					->where('page', $page)
					->first();

				if ($cached) {
					$results = $cached->results;
				} else {
					// 2. Fetch from API
					$response = Http::withHeaders([
						'x-rapidapi-host' => env('RAPIDAPI_HOST'),
						'x-rapidapi-key'  => env('RAPIDAPI_KEY'),
					])->get('https://goodreads12.p.rapidapi.com/searchBooks', [
						'keyword' => $query,
						'page' => $page
					]);

					if ($response->successful()) {
						$apiData = $response->json();

						// 3. Map and Upgrade Images
						$results = array_map(function ($item) {
							return [
								'id' => $item['bookId'],
								'title' => $item['title'],
								'author' => $item['author'][0]['name'] ?? 'Unknown Author',
								// APPLY IMAGE FIX HERE
								'cover' => $this->getHighResImage($item['imageUrl']),
								'rating' => $item['rating'] ?? 0,
								'description' => 'See details for description.',
								'published' => $item['publishedYear'] ?? 'N/A',
							];
						}, $apiData);

						// 4. Store in Cache
						SearchCache::create([
							'query' => $query,
							'page' => $page,
							'results' => $results
						]);
					}
				}
			}

			return view('books.search', compact('results', 'query', 'page'));
		}

		public function show($id)
		{
			// 1. Check Cache
			$cachedBook = BookCache::where('goodreads_id', $id)->first();

			if ($cachedBook) {
				$book = $cachedBook->data;
			} else {
				// 2. Fetch from API
				$response = Http::withHeaders([
					'x-rapidapi-host' => env('RAPIDAPI_HOST'),
					'x-rapidapi-key'  => env('RAPIDAPI_KEY'),
				])->get('https://goodreads12.p.rapidapi.com/getBookByID', [
					'bookID' => $id
				]);

				if ($response->successful()) {
					$rawData = $response->json();

					$pubDate = 'Unknown';
					if(isset($rawData['details']['publicationTime'])) {
						$pubDate = date('F j, Y', $rawData['details']['publicationTime'] / 1000);
					}

					$genres = [];
					if(isset($rawData['bookGenres'])) {
						foreach($rawData['bookGenres'] as $g) {
							$genres[] = $g['name'];
						}
					}

					$book = [
						'id' => $rawData['legacyId'] ?? $id,
						'title' => $rawData['title'],
						'author' => $rawData['author']['name'] ?? 'Unknown',
						'author_image' => $rawData['author']['profileImageUrl'] ?? null,
						'author_bio' => $rawData['author']['description'] ?? '',
						'description' => $rawData['description'] ?? 'No description available.',
						// APPLY IMAGE FIX HERE
						'imageURL' => $this->getHighResImage($rawData['imageUrl']),
						'rating' => $rawData['stats']['averageRating'] ?? 0,
						'ratings_count' => $rawData['stats']['ratingsCount'] ?? 0,
						'pages' => $rawData['details']['numPages'] ?? 'N/A',
						'publicationDate' => $pubDate,
						'publisher' => $rawData['details']['publisher'] ?? '',
						'genres' => $genres,
						'reviews' => $rawData['reviews'] ?? [],
						'amazonLink' => $rawData['links']['primaryAffiliateLink']['url'] ?? null
					];

					// 3. Save to Cache
					BookCache::create([
						'goodreads_id' => $id,
						'data' => $book
					]);
				} else {
					abort(404, 'Book details not found.');
				}
			}

			// Prepare object for Database Storage
			// Ensure we store the high-res image in the user's collection too
			$bookForDb = [
				'id' => $book['id'],
				'title' => $book['title'],
				'author' => $book['author'],
				'cover' => $book['imageURL'],
				'rating' => $book['rating']
			];

			$userSeries = Series::where('user_id', Auth::id())->get();

			return view('books.show', compact('book', 'bookForDb', 'userSeries'));
		}

		public function addToSeries(Request $request)
		{
			$request->validate([
				'series_name' => 'required_without:series_id',
				'book_json' => 'required'
			]);

			if ($request->series_id) {
				$series = Series::find($request->series_id);
			} else {
				$series = Series::create([
					'user_id' => Auth::id(),
					'title' => $request->series_name,
					'books' => []
				]);
			}

			$books = $series->books ?? [];
			$newBook = json_decode($request->book_json, true);

			$exists = false;
			foreach($books as $b) {
				if($b['id'] == $newBook['id']) $exists = true;
			}

			if(!$exists) {
				$books[] = $newBook;
				$series->books = $books;
				$series->save();
			}

			return redirect()->route('dashboard')->with('status', 'Book added to series!');
		}
	}
