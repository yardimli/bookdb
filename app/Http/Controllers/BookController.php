<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Book;
use App\Models\SearchCache;
use App\Models\BookCache;
use App\Models\SeriesNote;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\Genre;
use App\Models\UserBookStatus;
use App\Models\UserBookReadLog;

class BookController extends Controller
{
	/**
	 * Helper to upgrade Goodreads/Amazon low-res thumbnails to higher res.
	 */
	private function getHighResImage($url)
	{
		if (!$url)
			return 'https://placehold.co/300x450?text=No+Cover';

		// Pattern matches ._SX50_, ._SY75_, ._SX98_, etc.
		return preg_replace('/._S[XY]\d+_/', '._SY300_', $url);
	}

	public function index()
	{
		$userId = Auth::id();

		$series = Series::where('user_id', $userId)
			->with([
				'books' => function ($q) use ($userId) {
					$q->orderBy('series_book.sort_order')
						->with([
							'userStatuses' => function ($s) use ($userId) {
								$s->where('user_id', $userId);
							}
						]);
				},
				'notes',
				'genres'
			])
			->get();

		$genres = Genre::orderBy('name')->get();

		return view('dashboard', compact('series', 'genres'));
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
					'x-rapidapi-key' => env('RAPIDAPI_KEY'),
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
				'x-rapidapi-key' => env('RAPIDAPI_KEY'),
			])->get('https://goodreads12.p.rapidapi.com/getBookByID', [
						'bookID' => $id
					]);

			if ($response->successful()) {
				$rawData = $response->json();

				$pubDate = 'Unknown';
				if (isset($rawData['details']['publicationTime'])) {
					$pubDate = date('F j, Y', $rawData['details']['publicationTime'] / 1000);
				}

				$genres = [];
				if (isset($rawData['bookGenres'])) {
					foreach ($rawData['bookGenres'] as $g) {
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

		$book = json_decode($request->book_json, true);

		// 1. Insert or update book table
		$book = Book::firstOrCreate(
			['external_id' => $book['id'] ?? null],
			[
				'title' => $book['title'] ?? '',
				'author' => $book['author'] ?? null,
				'cover' => $book['cover'] ?? '',
				'rating' => $book['rating'] ?? null,
				'external_id' => $book['id'] ?? null,
				// add all fields you want to save
			]
		);

		//  2. Find or create the series
		if ($request->series_id) {
			$series = Series::findOrFail($request->series_id);
		} else {
			$series = Series::create([
				'user_id' => Auth::id(),
				'title' => $request->series_name,
			]);
		}

		//3. Attach book to pivot table (book_series)
		$series->books()->syncWithoutDetaching([$book->id]);

		return redirect()->route('dashboard')->with('status', 'Book added to series!');
	}

	public function removeFromSeries(Request $request)
	{
		$request->validate([
			'book_id' => 'required|exists:books,id',
			'series_id' => 'required|exists:series,id',
		]);

		$series = Series::where('id', $request->series_id)
			->where('user_id', Auth::id())
			->firstOrFail();

		// Remove from pivot table ONLY
		$series->books()->detach($request->book_id);

		return redirect()
			->route('dashboard')
			->with('status', 'Book removed from series.');
	}

	public function editSeries(Request $request)
	{
		$request->validate([
			'series_id' => 'required|exists:series,id',
			'title' => 'required|string|max:255',
			'description' => 'nullable|string',
			'notes.*.content' => 'nullable|string',
		]);

		$series = Series::where('id', $request->series_id)
			->where('user_id', Auth::id())
			->firstOrFail();

		$series->update([
			'title' => $request->title,
			'description' => $request->description,
		]);

		// Handle notes
		$existingIds = [];

		if ($request->notes) {
			foreach ($request->notes as $note) {
				if (!empty($note['id'])) {
					// Update
					SeriesNote::where('id', $note['id'])
						->where('series_id', $series->id)
						->update(['content' => $note['content']]);

					$existingIds[] = $note['id'];
				} elseif (!empty($note['content'])) {
					// Create
					$new = $series->notes()->create([
						'content' => $note['content'],
					]);
					$existingIds[] = $new->id;
				}
			}
		}

		// Delete removed notes
		$series->notes()
			->whereNotIn('id', $existingIds)
			->delete();

		// Handle Genre
		$genres = $request->genres;
		$genreIds = [];

		foreach ($genres as $name) {
			$genre = Genre::firstOrCreate(['name' => $name]);
			$genreIds[] = $genre->id;
		}

		// Sync genres to series
		$series->genres()->sync($genreIds);

		return redirect()
			->route('dashboard')
			->with('status', 'Series updated successfully.');
	}
	public function reorder(Request $request, Series $series)
	{
		$order = $request->order; // array of book IDs IN NEW ORDER

		foreach ($order as $index => $bookId) {
			$series->books()
				->updateExistingPivot($bookId, ['sort_order' => $index]);
		}

		return response()->json(['status' => 'ok']);
	}

	public function updateBookStatus(Request $request, Book $book)
	{
		$request->validate([
			'status' => 'required|in:unread,reading,read,dnf',
			'started_at' => 'nullable|date',
			'finished_at' => 'nullable|date',
			'dnf_at' => 'nullable|date',
		]);

		$userId = Auth::id();

		$ubs = UserBookStatus::firstOrCreate(
			['user_id' => $userId, 'book_id' => $book->id],
			['status' => 'unread']
		);

		$status = $request->status;

		// 基本規則：切換狀態時順手補日期（你也可更嚴格）
		$data = ['status' => $status];

		if ($status === 'reading') {
			$data['started_at'] = $request->started_at ?? ($ubs->started_at ?? now()->toDateString());
			$data['finished_at'] = null;
			$data['dnf_at'] = null;
		}

		if ($status === 'read') {
			$data['finished_at'] = $request->finished_at ?? now()->toDateString();
			$data['dnf_at'] = null;

			// 寫入一次「閱讀完成紀錄」：允許多次閱讀
			UserBookReadLog::create([
				'user_id' => $userId,
				'book_id' => $book->id,
				'started_at' => $ubs->started_at,
				'finished_at' => $data['finished_at'],
			]);

			// 讀完就把進度拉滿（可選）
			if ($ubs->progress_percent !== null)
				$data['progress_percent'] = 100;
		}

		if ($status === 'dnf') {
			$data['dnf_at'] = $request->dnf_at ?? now()->toDateString();
			$data['finished_at'] = null;
		}

		if ($status === 'unread') {
			$data['started_at'] = null;
			$data['finished_at'] = null;
			$data['dnf_at'] = null;
			$data['progress_page'] = null;
			$data['progress_percent'] = null;
		}

		$ubs->update($data);

		return back()->with('status', 'Book status updated.');
	}

	public function updateBookProgress(Request $request, Book $book)
	{
		$request->validate([
			'progress_page' => 'nullable|integer|min:0',
			'progress_percent' => 'nullable|integer|min:0|max:100',
		]);

		$userId = Auth::id();

		$ubs = UserBookStatus::firstOrCreate(
			['user_id' => $userId, 'book_id' => $book->id],
			['status' => 'unread']
		);

		// 只要更新進度，就自動把狀態調成 reading（你可改）
		$data = [
			'progress_page' => $request->progress_page,
			'progress_percent' => $request->progress_percent,
		];

		if ($ubs->status === 'unread') {
			$data['status'] = 'reading';
			$data['started_at'] = $ubs->started_at ?? now()->toDateString();
		}

		// 100% 自動 read（可選）
		if ($request->progress_percent === 100) {
			$data['status'] = 'read';
			$data['finished_at'] = now()->toDateString();

			UserBookReadLog::create([
				'user_id' => $userId,
				'book_id' => $book->id,
				'started_at' => $ubs->started_at,
				'finished_at' => $data['finished_at'],
			]);
		}

		$ubs->update($data);

		return back()->with('status', 'Progress updated.');
	}

	public function saveBookStatus(Request $request)
	{
		$request->validate([
			'book_id' => 'required|exists:books,id',
			'status' => 'required|in:unread,reading,read,dnf',
			'progress_page' => 'nullable|integer|min:0',
			'progress_percent' => 'nullable|integer|min:0|max:100',
			'started_at' => 'nullable|date',
			'finished_at' => 'nullable|date',
			'dnf_at' => 'nullable|date',
		]);

		$userId = Auth::id();
		$bookId = (int) $request->book_id;

		$ubs = UserBookStatus::firstOrCreate(
			['user_id' => $userId, 'book_id' => $bookId],
			['status' => 'unread']
		);

		$status = $request->status;

		$data = [
			'status' => $status,
			'progress_page' => $request->progress_page,
			'progress_percent' => $request->progress_percent,
			'started_at' => $request->started_at,
			'finished_at' => $request->finished_at,
			'dnf_at' => $request->dnf_at,
		];

		if ($status === 'unread') {
			$data['progress_page'] = null;
			$data['progress_percent'] = null;
			$data['started_at'] = null;
			$data['finished_at'] = null;
			$data['dnf_at'] = null;
		}

		if ($status === 'reading') {
			$data['finished_at'] = null;
			$data['dnf_at'] = null;
			$data['started_at'] = $data['started_at'] ?? ($ubs->started_at ?? now()->toDateString());
		}

		if ($status === 'dnf') {
			$data['finished_at'] = null;
			$data['dnf_at'] = $data['dnf_at'] ?? now()->toDateString();
		}

		if ($status === 'read') {
			$data['dnf_at'] = null;
			$data['finished_at'] = $data['finished_at'] ?? now()->toDateString();

			// 可選：percent 直接拉滿
			if ($data['progress_percent'] !== null)
				$data['progress_percent'] = 100;

			// 寫入閱讀完成紀錄（允許多次）
			UserBookReadLog::create([
				'user_id' => $userId,
				'book_id' => $bookId,
				'started_at' => $data['started_at'] ?? $ubs->started_at,
				'finished_at' => $data['finished_at'],
			]);
		}

		$ubs->update($data);

		return back()->with('status', 'Book status saved.');
	}

}
