<div class="field">
    <label for="title">Book title</label>
    <input id="title" name="title" type="text" value="{{ old('title', $book->title ?? '') }}" maxlength="255" required>
</div>
<div class="field">
    <label for="author">Author</label>
    <input id="author" name="author" type="text" value="{{ old('author', $book->author ?? '') }}" maxlength="255" required>
</div>
<div class="field">
    <label for="category">Category</label>
    <select id="category" name="category" required>
        <option value="">Choose a category</option>
        @foreach ($categories as $category)
            <option value="{{ $category }}" @selected(old('category', $book->category ?? '') === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>
<div class="field">
    <label for="unit_price">Unit price (PHP)</label>
    <input id="unit_price" name="unit_price" type="number" min="0" step="0.01" value="{{ old('unit_price', $book->unit_price ?? '') }}" required>
</div>
<div class="field">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4" required>{{ old('description', $book->description ?? '') }}</textarea>
</div>
<div class="field">
    <label for="availability_status">Availability</label>
    <select id="availability_status" name="availability_status" required>
        <option value="">Choose availability</option>
        @foreach ($availabilityStatuses as $availabilityStatus)
            <option value="{{ $availabilityStatus }}" @selected(old('availability_status', $book->availability_status ?? '') === $availabilityStatus)>{{ $availabilityStatus }}</option>
        @endforeach
    </select>
</div>
