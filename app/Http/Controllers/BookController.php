<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = $request->user()->books()->latest()->paginate(12);

        return view('books.index', compact('books'));
    }

    public function create(): View
    {
        return view('books.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        // Create through the authenticated user's relationship so user_id is never accepted from the request.
        $request->user()->books()->create($validated);

        return redirect()->route('books.index')->with('status', 'Book added successfully.');
    }

    public function show(Book $book): View
    {
        $this->authorizeBook($book);

        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $this->authorizeBook($book);

        return view('books.edit', $this->formOptions() + compact('book'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $this->authorizeBook($book);
        $book->update($request->validate($this->rules()));

        return redirect()->route('books.show', $book)->with('status', 'Book updated successfully.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorizeBook($book);
        $book->delete();

        return redirect()->route('books.index')->with('status', 'Book deleted successfully.');
    }

    private function authorizeBook(Book $book): void
    {
        abort_unless($book->user_id === auth()->id(), 404);
    }

    private function formOptions(): array
    {
        return [
            'categories' => Book::CATEGORIES,
            'availabilityStatuses' => Book::AVAILABILITY_STATUSES,
        ];
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Book::CATEGORIES)],
            'unit_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'description' => ['required', 'string'],
            'availability_status' => ['required', Rule::in(Book::AVAILABILITY_STATUSES)],
        ];
    }
}
