@extends('layouts.app')

@section('title', 'Books')

@section('content')
    <div class="page-heading">
        <div><h1>Book inventory</h1><p>Manage your bookstore's saved records.</p></div>
        <a class="button" href="{{ route('books.create') }}">Add a book</a>
    </div>
    @if ($books->isEmpty())
        <div class="card empty">No books have been added yet.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Unit price</th><th>Availability</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td>{{ $book->title }}</td><td>{{ $book->author }}</td><td>{{ $book->category }}</td>
                            <td>₱{{ number_format((float) $book->unit_price, 2) }}</td>
                            <td><span class="status-pill {{ strtolower($book->availability_status) }}">{{ $book->availability_status }}</span></td>
                            <td class="actions">
                                <a href="{{ route('books.show', $book) }}">View</a>
                                <a href="{{ route('books.edit', $book) }}">Edit</a>
                                <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Delete this book record? This action cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button class="link-button danger-text" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $books->links() }}</div>
    @endif
@endsection
