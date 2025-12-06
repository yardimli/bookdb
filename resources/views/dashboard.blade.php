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
                            
                            <div id="sortable-{{ $collection->id }}" class="carousel carousel-center max-w-full p-4 space-x-4 bg-base-200/50 rounded-box min-h-[200px]">
                                @if($collection->books->count())
                                    @foreach($collection->books as $book)
                                        <a href="/book/{{ $book->external_id }}" target="_blank"  data-id="{{ $book->id }}">
                                            <div class="carousel-item flex flex-col w-32 gap-2 transition-transform hover:scale-105" style="position: relative;">
                                                {{-- Top-left number badge --}}
                                                <span class="book-index-badge">
                                                    {{ $loop->iteration }}
                                                </span>
                                                <img src="{{ $book->cover }}" class="rounded-box h-48 object-cover shadow-sm" />
                                                <span class="text-xs font-bold truncate text-center">{{ $book->title }}</span>
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

<style>
.book-index-badge {
    position: absolute;
    top: -10px;
    left: -10px;
    background-color: #1e40af; /* navy blue */
    color: white;
    font-size: 0.65rem;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 9999px; /* fully rounded */
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    z-index: 10;
}

</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[id^='sortable-']").forEach(el => {
        new Sortable(el, {
            animation: 150,
            ghostClass: "opacity-50",
            onEnd: function (evt) {
                let seriesId = el.id.replace("sortable-", "");

                let order = [...el.children].map(child => child.getAttribute("data-id"));

                // update the index badge
                el.querySelectorAll('.carousel-item').forEach((item, idx) => {
                    const badge = item.querySelector('.book-index-badge');
                    if (badge) {
                        badge.textContent = idx + 1; // counting starts from 1
                    }
                });
                
                // Send new order to Laravel
                fetch(`/series/${seriesId}/reorder`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify({ order: order })
                });
            }
        })
    });
});
</script>

