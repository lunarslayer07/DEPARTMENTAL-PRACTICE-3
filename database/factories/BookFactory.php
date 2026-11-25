<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'category' => fake()->randomElement(Book::CATEGORIES),
            'unit_price' => fake()->randomFloat(2, 0, 1000),
            'description' => fake()->paragraph(),
            'availability_status' => fake()->randomElement(Book::AVAILABILITY_STATUSES),
        ];
    }
}
