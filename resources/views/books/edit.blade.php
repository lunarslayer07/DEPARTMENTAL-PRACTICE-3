@extends('layouts.app')

@section('title', 'Edit book')

@section('content')
    <section class="card narrow">
        <h1>Edit book</h1>
        <form method="POST" action="{{ route('books.update', $book) }}">
            @csrf @method('PUT')
            @include('books._form')
            <div class="form-actions"><button type="submit">Save changes</button><a class="button secondary" href="{{ route('books.show', $book) }}">Cancel</a></div>
        </form>
    </section>
@endsection
