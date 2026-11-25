@extends('layouts.app')

@section('title', 'Inventory reports')

@section('content')
    <h1>Inventory reports</h1>
    <p>Filter saved books by category and availability. Exports use the same matching records.</p>
    <section class="card">
        <form method="GET" action="{{ route('reports.index') }}" class="filter-form">
            <div class="field">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="availability_status">Availability</label>
                <select id="availability_status" name="availability_status">
                    <option value="">All statuses</option>
                    @foreach ($availabilityStatuses as $availabilityStatus)
                        <option value="{{ $availabilityStatus }}" @selected(($filters['availability_status'] ?? '') === $availabilityStatus)>{{ $availabilityStatus }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit">Apply filters</button>
            <a class="button secondary" href="{{ route('reports.index') }}">Reset filters</a>
        </form>
    </section>
    <div class="page-heading report-heading">
        <h2>Matching books <span class="count">({{ $books->count() }})</span></h2>
        <div class="export-links">
            <a class="button secondary" href="{{ route('reports.export.pdf', $filters) }}">Export PDF</a>
            <a class="button secondary" href="{{ route('reports.export.excel', $filters) }}">Export Excel</a>
        </div>
    </div>
    @if ($books->isEmpty())
        <div class="card empty">No books match the selected filters.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Unit price</th><th>Availability</th></tr></thead>
                <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td>{{ $book->title }}</td><td>{{ $book->author }}</td><td>{{ $book->category }}</td>
                            <td>₱{{ number_format((float) $book->unit_price, 2) }}</td>
                            <td><span class="status-pill {{ strtolower($book->availability_status) }}">{{ $book->availability_status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
