<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BookReport
{
    /**
     * Apply both optional report filters to the authenticated user's saved books.
     */
    public function query(User $user, array $filters): Builder
    {
        return Book::query()
            ->where('user_id', $user->id)
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['availability_status'] ?? null, fn (Builder $query, string $status) => $query->where('availability_status', $status))
            ->orderBy('title');
    }

    public function validateFilters(array $filters): array
    {
        return validator($filters, [
            'category' => ['nullable', 'string', \Illuminate\Validation\Rule::in(Book::CATEGORIES)],
            'availability_status' => ['nullable', 'string', \Illuminate\Validation\Rule::in(Book::AVAILABILITY_STATUSES)],
        ])->validate();
    }
}
