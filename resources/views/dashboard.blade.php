<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Header with Title and Search Bar -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
            <h2 class="text-3xl font-bold">My Book Series</h2>
            
            <!-- Search Form -->
            <form method="GET" action="{{ route('books.search') }}" class="join w-full md:w-auto">
                <input
                  type="text"
                  name="q"
                  class="input input-bordered join-item w-full md:w-64"
                  placeholder="Search for new books..."
                />
                <button class="btn btn-primary join-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </button>
            </form>
        </div>
        
        @if($series->isEmpty())
            <div class="hero bg-base-200 rounded-box p-10 border border-base-300">
                <div class="hero-content text-center">
                    <div class="max-w-md">
                        <h1 class="text-2xl font-bold">No collections yet</h1>
                        <p class="py-6 opacity-70">Use the search bar above to find books and start building your series collection.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="grid gap-6">
                @foreach($series as $collection)
                    <div class="card bg-base-100 shadow-md border border-base-200">
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <h3 class="card-title text-xl">{{ $collection->title }}</h3>
                                <div class="badge badge-ghost">{{ count($collection->books ?? []) }} books</div>
                            </div>
                            <div class="divider my-1"></div>
                            
                            <div class="carousel carousel-center max-w-full p-4 space-x-4 bg-base-200/50 rounded-box min-h-[200px]">
                                @if(!empty($collection->books))
                                    @foreach($collection->books as $book)
                                        <a href="/book/{{ $book['id'] }}" target="_blank">
                                            <div class="carousel-item flex flex-col w-32 gap-2 transition-transform hover:scale-105">
                                                <img src="{{ $book['cover'] }}" class="rounded-box h-48 object-cover shadow-sm" />
                                                <span class="text-xs font-bold truncate text-center">{{ $book['title'] }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <div class="flex w-full justify-center items-center opacity-50 italic text-sm">
                                        No books in this series yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
