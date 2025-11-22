<x-app-layout>
	<div class="max-w-6xl mx-auto space-y-8">
		
		<!-- Top Section: Cover & Key Details -->
		<div class="card lg:card-side bg-base-100 shadow-xl border border-base-200 overflow-hidden">
			<figure class="lg:w-1/3 bg-base-200 p-6 flex justify-center items-start relative">
				<!-- Blurry background effect -->
				<div class="absolute inset-0 bg-cover bg-center blur-xl opacity-20" style="background-image: url('{{ $book['imageURL'] }}')"></div>
				<img src="{{ $book['imageURL'] }}" alt="{{ $book['title'] }}" class="rounded-lg shadow-2xl max-w-[200px] w-full object-cover z-10 relative" />
			</figure>
			<div class="card-body lg:w-2/3">
				<!-- Title & Author -->
				<div>
					<h1 class="text-4xl font-bold font-serif">{{ $book['title'] }}</h1>
					<div class="flex items-center gap-2 mt-2">
						<span class="text-lg opacity-70">by</span>
						<span class="text-xl font-semibold text-primary">{{ $book['author'] }}</span>
					</div>
				</div>
				
				<!-- Ratings & Stats -->
				<div class="flex flex-wrap gap-4 my-4">
					<div class="badge badge-lg badge-warning gap-2 p-4">
						<span class="font-bold">{{ $book['rating'] }}</span> ★
						<span class="text-xs opacity-70 border-l border-black/10 pl-2 ml-1">
                            {{ number_format($book['ratings_count']) }} votes
                        </span>
					</div>
					<div class="badge badge-lg badge-outline p-4">{{ $book['publicationDate'] }}</div>
					<div class="badge badge-lg badge-outline p-4">{{ $book['pages'] }} Pages</div>
				</div>
				
				<!-- Genres -->
				@if(isset($book['genres']) && count($book['genres']) > 0)
					<div class="flex flex-wrap gap-2 mb-4">
						@foreach(array_slice($book['genres'], 0, 8) as $genre)
							<a href="{{ route('books.search', ['q' => $genre]) }}" class="badge badge-ghost hover:badge-neutral cursor-pointer transition-colors">
								{{ $genre }}
							</a>
						@endforeach
					</div>
				@endif
				
				<!-- Description -->
				<div class="prose max-w-none text-base-content/80 text-sm">
					<div class="line-clamp-4">
						{!! $book['description'] !!}
					</div>
					<button onclick="document.getElementById('desc_modal').showModal()" class="btn btn-link btn-xs p-0 no-underline mt-1">Read Full Description</button>
				</div>
				
				<!-- Actions -->
				<div class="card-actions justify-end mt-auto pt-6 border-t border-base-200">
					@if(isset($book['amazonLink']))
						<a href="{{ $book['amazonLink'] }}" target="_blank" class="btn btn-ghost gap-2">
							Buy on Amazon
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
						</a>
					@endif
					<button class="btn btn-primary" onclick="document.getElementById('add_modal').showModal()">
						+ Add to Collection
					</button>
				</div>
			</div>
		</div>
		
		<!-- Author Section -->
		<div class="card bg-base-100 shadow-md border border-base-200">
			<div class="card-body flex-row items-center gap-6">
				@if($book['author_image'])
					<div class="avatar">
						<div class="w-20 h-20 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
							<img src="{{ $book['author_image'] }}" />
						</div>
					</div>
				@else
					<div class="avatar placeholder">
						<div class="bg-neutral text-neutral-content rounded-full w-20">
							<span class="text-2xl">{{ substr($book['author'], 0, 1) }}</span>
						</div>
					</div>
				@endif
				<div>
					<h3 class="card-title">About {{ $book['author'] }}</h3>
					@if($book['author_bio'])
						<p class="text-sm mt-2 text-base-content/70 line-clamp-2">{!! $book['author_bio'] !!}</p>
					@else
						<p class="text-sm mt-2 text-base-content/50 italic">Author biography not available.</p>
					@endif
				</div>
			</div>
		</div>
		
		<!-- Reviews Section -->
		<div class="space-y-6">
			<div class="flex items-center gap-2">
				<h3 class="text-2xl font-bold">Community Reviews</h3>
				<div class="badge badge-neutral">{{ count($book['reviews']) }}</div>
			</div>
			
			@if(isset($book['reviews']) && count($book['reviews']) > 0)
				<div class="grid grid-cols-1 gap-6">
					@foreach($book['reviews'] as $review)
						<div class="card bg-base-100 shadow-sm border border-base-200 break-inside-avoid">
							<div class="card-body p-6">
								<div class="flex justify-between items-start mb-4">
									<div class="flex items-center gap-3">
										<!-- Generated Avatar based on User Ref ID -->
										@php
											// Extract a pseudo ID for avatar generation
											$userId = Str::after($review['creator']['__ref'] ?? 'User:0', 'User:');
										@endphp
										<div class="avatar">
											<div class="w-10 h-10 rounded-full bg-base-300">
												<img src="https://api.dicebear.com/9.x/thumbs/svg?seed={{ $userId }}" alt="Avatar" />
											</div>
										</div>
										
										<div>
											<h4 class="font-bold text-sm">Goodreads Reviewer</h4>
											<div class="flex items-center gap-2 text-xs opacity-60 mt-0.5">
												<span>{{ $review['rating'] }} ★</span>
												<span>•</span>
												{{-- Convert timestamp to readable date --}}
												<span>{{ date('M d, Y', $review['createdAt'] / 1000) }}</span>
											</div>
										</div>
									</div>
									
									<div class="badge badge-ghost badge-sm gap-1">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path d="M1 8.25a1.25 1.25 0 112.5 0v7.5a1.25 1.25 0 11-2.5 0v-7.5zM11 3V1.7c0-.268.14-.526.395-.607A2 2 0 0114 3c0 .995-.182 1.948-.514 2.826-.204.54.166 1.174.744 1.174h2.52c1.243 0 2.261 1.01 2.146 2.247a23.864 23.864 0 01-1.341 5.974C17.153 16.323 16.072 17 14.9 17h-3.192a3 3 0 01-3-3v-6c0-.865.36-1.686 1.03-2.277l1.262-1.105z"/></svg>
										{{ $review['likeCount'] ?? 0 }}
									</div>
								</div>
								
								<!-- Review Content -->
								<div class="text-sm text-base-content/80 leading-relaxed space-y-2 review-content">
									{{-- The API returns HTML in reviews --}}
									{!! $review['text'] !!}
								</div>
							</div>
						</div>
					@endforeach
				</div>
			@else
				<div class="alert alert-ghost">No reviews available for this book.</div>
			@endif
		</div>
	</div>
	
	<!-- Full Description Modal -->
	<dialog id="desc_modal" class="modal">
		<div class="modal-box w-11/12 max-w-3xl">
			<h3 class="font-bold text-lg mb-4">{{ $book['title'] }}</h3>
			<div class="py-4 leading-relaxed prose">
				{!! $book['description'] !!}
			</div>
			<div class="modal-action">
				<form method="dialog">
					<button class="btn">Close</button>
				</form>
			</div>
		</div>
	</dialog>
	
	<!-- Add to Collection Modal -->
	<dialog id="add_modal" class="modal">
		<div class="modal-box">
			<h3 class="font-bold text-lg">Add to Collection</h3>
			<p class="py-4 text-sm opacity-70">Select an existing series or create a new one.</p>
			
			<form method="POST" action="{{ route('series.add') }}">
				@csrf
				<input type="hidden" name="book_json" value="{{ json_encode($bookForDb) }}">
				
				<div class="form-control w-full">
					<label class="label"><span class="label-text">Existing Series</span></label>
					<select name="series_id" id="series_select" class="select select-bordered w-full">
						<option value="">-- Create New Series --</option>
						@foreach($userSeries as $s)
							<option value="{{ $s->id }}">{{ $s->title }}</option>
						@endforeach
					</select>
				</div>
				
				<div class="form-control w-full mt-4" id="new_series_input">
					<label class="label"><span class="label-text">New Series Name</span></label>
					<input type="text" name="series_name" placeholder="e.g., Summer Reads" class="input input-bordered w-full" />
				</div>
				
				<div class="modal-action">
					<button type="button" class="btn" onclick="document.getElementById('add_modal').close()">Cancel</button>
					<button type="submit" class="btn btn-primary">Save Book</button>
				</div>
			</form>
		</div>
	</dialog>
	
	<script>
		// Toggle new series input visibility
		const seriesSelect = document.getElementById('series_select');
		const newSeriesInput = document.getElementById('new_series_input');
		
		seriesSelect.addEventListener('change', function() {
			if(this.value === "") {
				newSeriesInput.style.display = 'block';
			} else {
				newSeriesInput.style.display = 'none';
			}
		});
	</script>
</x-app-layout>
