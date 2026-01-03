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

        {{-- Genre Filters --}}
        <div class="flex flex-wrap gap-2 mb-6" id="genre-filters">
            <button class="genre-filter-btn active" data-genre="ALL" onclick="filterByGenre('ALL', this)">
                ALL
            </button>

            @foreach($genres as $genre)
                <button class="genre-filter-btn" data-genre="{{ $genre->name }}"
                    onclick="filterByGenre('{{ $genre->name }}', this)">
                    {{ $genre->name }}
                </button>
            @endforeach
        </div>

        <div id="no-series-message" class="text-center text-gray-500 italic mb-4" style="display: none;">
            No series found in this genre.
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
                    <div class="card bg-base-100 shadow-md border border-base-200 series-card"
                        data-genres='@json($collection->genres->pluck("name"))'>
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <div class="flex justify-between items-start" style="align-items: anchor-center;">
                                    <h3 class="card-title text-xl">{{ $collection->title }}</h3>
                                    <!-- Edit icon -->
                                    <button
                                        type="button"
                                        class="text-gray-500 hover:text-primary transition"
                                        title="Edit series"
                                        data-series-id="{{ $collection->id }}"
                                        data-series-title="{{ $collection->title }}"
                                        data-series-description="{{ $collection->description }}"
                                        data-series-notes='@json($collection->notes->map(fn($n) => [
            "id" => $n->id,
            "content" => $n->content
        ]))'
                                        data-series-genres='@json(
            $collection->genres->pluck("name")
        )'
                                        onclick="openEditSeriesModal(this)"
                                        style="cursor: pointer"
                                    >
                                        &nbsp;✏️
                                    </button>
                                </div>
                                <div class="badge badge-ghost">{{ count($collection->books ?? []) }} books</div>
                            </div>
                            <div class="divider my-1"></div>
                            
                            <div id="sortable-{{ $collection->id }}" class="carousel carousel-center max-w-full p-4 space-x-4 bg-base-200/50 rounded-box min-h-[200px]">
                                @if($collection->books->count())
                                    @foreach($collection->books as $book)
                                        <a href="/book/{{ $book->external_id }}" target="_blank"  data-id="{{ $book->id }}">
                                            <div class="carousel-item flex flex-col w-32 gap-2 transition-transform hover:scale-105" style="position: relative;">
                                                {{-- Top-left number badge --}}
                                                <span class="book-delete-badge" 
                                                data-book-id="{{ $book->id }}" data-series-id="{{ $collection->id }}">
                                                    X
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

    <!-- Remove from Collection Modal -->
	<dialog id="remove_book_modal" class="modal">
		<div class="modal-box">
			<h3 class="font-bold text-lg">Remove Book</h3>
			<p class="py-4 text-sm opacity-70">Remove book from the series.</p>
			<form method="POST" action="{{ route('book.remove') }}">
				@csrf
                <!-- REQUIRED -->
                <input type="hidden" name="book_id" id="remove_book_id">
                <input type="hidden" name="series_id" id="remove_series_id">
				<div class="modal-action">
					<button type="button" class="btn" onclick="document.getElementById('remove_book_modal').close()">Cancel</button>
					<button type="submit" class="btn btn-danger">Remove</button>
				</div>
			</form>
		</div>
	</dialog>

<dialog id="edit_series_modal" class="modal modal-lg">
    <div class="modal-box w-[75vw] max-w-none h-[80vh] overflow-y-auto" style="width: calc(75vw)">
        <h2 class="font-bold text-lg">Edit Series</h2>
        <hr style="margin: 10px 0px;">

        <form method="POST" action="{{ route('series.edit') }}">
            @csrf

            <input type="hidden" name="series_id" id="edit_series_id">

            {{-- Series title --}}
            <div class="form-control mb-4">
                <h4 class="font-semibold mb-3">Series Name</h4>
                <input type="text" name="title" id="edit_series_title" class="input input-bordered w-full" required>
            </div>

            {{-- Description --}}
            <div class="form-control mb-4">
                <h4 class="font-semibold mb-3">Description</h4>
                <input type="text" name="description" id="edit_series_description" class="input input-bordered w-full">
            </div>
            
            {{-- Genres --}}
            <div class="form-control mb-4">
                <h4 class="font-semibold mb-3">Genres</h4>
            
                <select id="genres-select" name="genres[]" multiple class="w-full" placeholder="Select or type genres...">
                    @foreach($genres as $genre)
                        <option value="{{ $genre->name }}">{{ $genre->name }}</option>
                    @endforeach
                </select>
            </div>
            
            {{-- Notes --}}
            <div class="mb-4">
                <h4 class="font-semibold mb-3">Notes</h4>

                <div id="series_notes_container" class="flex flex-wrap gap-4"></div>

                <button type="button"
                        class="btn btn-sm btn-outline mt-2"
                        onclick="addSeriesNote()">
                    ➕ Add Note
                </button>
            </div>

            <div class="modal-action">
                <button type="button" class="btn" onclick="document.getElementById('edit_series_modal').close()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </div>
        </form>
    </div>
</dialog>


</x-app-layout>

<style>
.genre-filter-btn {
    padding: 6px 14px;
    border-radius: 999px;
    border: 1px solid #d1d5db;
    background: white;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.genre-filter-btn:hover {
    background: #f3f4f6;
}

.genre-filter-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.book-delete-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background-color: #878787ff; /* navy blue */
    color: white;
    font-size: 0.8rem;
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 9999px; /* fully rounded */
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    z-index: 10;
}
.series-note {
    position: relative;
    background-color: #fff9b1; /* soft yellow */
    width: 225px;
    height: 150px;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
}

.series-note textarea {
    background: transparent;
    border: none;
    resize: none;
    flex: 1;
    font-size: 0.9rem;
}

.series-note textarea:focus {
    outline: none;
}

.series-note .delete-note-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    background-color: #ef4444; /* red */
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>


<script>
let noteIndex = 0;

let genreSelect = new TomSelect('#genres-select', {
    plugins: ['remove_button'],
    persist: false,
    create: true,               // 🔥 allow free typing
    createOnBlur: true,
    maxItems: null,             // unlimited
    valueField: 'value',
    labelField: 'text',
    searchField: ['text'],
    placeholder: 'Select or type genres...',
});


function openEditSeriesModal(button) {
    document.getElementById('edit_series_id').value = button.dataset.seriesId;
    document.getElementById('edit_series_title').value = button.dataset.seriesTitle;
    document.getElementById('edit_series_description').value =
        button.dataset.seriesDescription || '';

    const container = document.getElementById('series_notes_container');
    container.innerHTML = '';
    noteIndex = 0;

    let notes = [];
    try {
        notes = button.dataset.seriesNotes
            ? JSON.parse(button.dataset.seriesNotes)
            : [];
    } catch (e) {
        console.error('Invalid notes JSON', e);
    }

    notes.forEach(note => {
        addSeriesNote(note.id, note.content);
    });


    // ---- genres (FIXED) ----
    genreSelect.clear(true);   // clear selected items ONLY

    let genres = [];
    try {
        genres = button.dataset.seriesGenres
            ? JSON.parse(button.dataset.seriesGenres)
            : [];
    } catch (e) {}

    genres.forEach(name => {
        genreSelect.addItem(name); // ✅ select only
    });

    document.getElementById('edit_series_modal').showModal();
}

function addSeriesNote(id = '', content = '') {
    const container = document.getElementById('series_notes_container');

    const wrapper = document.createElement('div');
    wrapper.className = 'series-note relative';

    wrapper.innerHTML = `
        <input type="hidden" name="notes[${noteIndex}][id]" value="${id}">
        <button type="button" class="delete-note-btn" onclick="this.parentElement.remove()">✕</button>
        <textarea
            name="notes[${noteIndex}][content]"
            placeholder="Write a note..."
        >${content}</textarea>
    `;

    container.appendChild(wrapper);
    noteIndex++;
}

function filterByGenre(genre, button) {
    const cards = document.querySelectorAll('.series-card');
    let anyVisible = false;

    cards.forEach(card => {
        const genres = JSON.parse(card.dataset.genres || '[]');

        if (genre === 'ALL' || genres.includes(genre)) {
            card.style.display = '';
            anyVisible = true;
        } else {
            card.style.display = 'none';
        }
    });

    // Show/hide “no series” message
    const noSeriesMsg = document.getElementById('no-series-message');
    noSeriesMsg.style.display = anyVisible ? 'none' : '';

    // update active button state
    document.querySelectorAll('.genre-filter-btn')
        .forEach(btn => btn.classList.remove('active'));

    button.classList.add('active');
}

document.querySelectorAll('.book-delete-badge').forEach((badge) => {
    badge.addEventListener('click', function (e) {
    const badge = e.target;
    if (!badge) return;

    e.stopPropagation(); // ⛔ stop bubbling
    e.preventDefault(); // ⛔ stop <a> navigation
      
    document.getElementById('remove_book_id').value = badge.dataset.bookId;
    document.getElementById('remove_series_id').value = badge.dataset.seriesId;

    document.getElementById('remove_book_modal').showModal();
    })
});

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
                    const badge = item.querySelector('.book-delete-badge');
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

