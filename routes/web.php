<?php

	use App\Http\Controllers\ProfileController;
	use App\Http\Controllers\BookController;
	use Illuminate\Support\Facades\Route;

	Route::get('/', function () {
		return view('welcome');
	});

	Route::middleware('auth')->group(function () {
		Route::get('/dashboard', [BookController::class, 'index'])->name('dashboard');

		// Book Routes
		Route::get('/search', [BookController::class, 'search'])->name('books.search');
		Route::get('/book/{id}', [BookController::class, 'show'])->name('books.show');
		Route::post('/series/add', [BookController::class, 'addToSeries'])->name('series.add');

		// Profile
		Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
		Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
		Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
	});

	require __DIR__ . '/auth.php';
