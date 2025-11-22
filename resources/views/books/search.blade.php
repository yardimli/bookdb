<x-app-layout>
	<div class="max-w-7xl mx-auto">
		<div class="flex justify-between items-center mb-6">
			<h2 class="text-3xl font-bold">Search Books</h2>
		</div>
		
		<form method="GET" action="{{ route('books.search') }}" class="join w-full max-w-lg mb-8">
			<input type="hidden" name="page" value="1"> <!-- Reset page on new search -->
			<input class="input input-bordered join-item w-full" name="q" placeholder="Search by title (e.g. Harry Potter)..." value="{{ $query ?? '' }}"/>
			<button class="btn join-item btn-primary">Search</button>
		</form>
		
		@if(isset($results) && count($results) > 0)
			<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
				@foreach($results as $book)
					<a href="{{ route('books.show', $book['id']) }}" class="card bg-base-100 shadow hover:shadow-lg hover:-translate-y-1 transition-all duration-300 border border-base-200 group">
						<figure class="px-4 pt-4">
							<img src="{{ $book['cover'] }}" alt="{{ $book['title'] }}" class="rounded-xl h-48 object-cover shadow-md" />
						</figure>
						<div class="card-body p-4 items-center text-center">
							<h2 class="card-title text-sm line-clamp-2 group-hover:text-primary transition-colors">{{ $book['title'] }}</h2>
							<p class="text-xs text-base-content/70 truncate w-full">{{ $book['author'] }}</p>
							<div class="badge badge-sm badge-ghost mt-2">{{ $book['rating'] }} ★</div>
						</div>
					</a>
				@endforeach
			</div>
			
			<!-- Simple Pagination -->
			<div class="join grid grid-cols-2 w-64 mx-auto mt-10">
				@if($page > 1)
					<a href="{{ route('books.search', ['q' => $query, 'page' => $page - 1]) }}" class="join-item btn btn-outline">Previous</a>
				@else
					<button class="join-item btn btn-outline btn-disabled">Previous</button>
				@endif
				
				<a href="{{ route('books.search', ['q' => $query, 'page' => $page + 1]) }}" class="join-item btn btn-outline">Next</a>
			</div>
		@elseif(isset($query))
			<div class="alert alert-info">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
				<span>No results found for "{{ $query }}". Try a different term.</span>
			</div>
		@endif
	</div>
</x-app-layout>
