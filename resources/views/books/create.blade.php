@extends('layouts.app')

@section('title', 'Add a book')

@section('content')
    <section class="card narrow">
        <h1>Add a book</h1>
        <form method="POST" action="{{ route('books.store') }}">
            @csrf
            @include('books._form')
            <div class="form-actions"><button type="submit">Save book</button><a class="button secondary" href="{{ route('books.index') }}">Cancel</a></div>
        </form>
    </section>
@endsection
