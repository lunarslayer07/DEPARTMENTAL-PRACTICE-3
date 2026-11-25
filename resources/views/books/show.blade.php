@extends('layouts.app')

@section('title', $book->title)

@section('content')
    <section class="card narrow">
        <div class="page-heading"><h1>{{ $book->title }}</h1><a class="button secondary" href="{{ route('books.edit', $book) }}">Edit</a></div>
        <dl class="details">
            <dt>Author</dt><dd>{{ $book->author }}</dd>
            <dt>Category</dt><dd>{{ $book->category }}</dd>
            <dt>Unit price</dt><dd>₱{{ number_format((float) $book->unit_price, 2) }}</dd>
            <dt>Availability</dt><dd><span class="status-pill {{ strtolower($book->availability_status) }}">{{ $book->availability_status }}</span></dd>
            <dt>Description</dt><dd>{{ $book->description }}</dd>
        </dl>
        <a href="{{ route('books.index') }}">Back to books</a>
    </section>
@endsection
