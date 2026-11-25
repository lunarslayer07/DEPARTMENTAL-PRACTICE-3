<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_view_and_delete_their_book(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attributes = [
            'title' => 'The Example Book',
            'author' => 'A. Writer',
            'category' => 'Academic',
            'unit_price' => '125.50',
            'description' => 'A short description.',
            'availability_status' => 'Available',
            'user_id' => User::factory()->create()->id,
        ];

        $this->post(route('books.store'), $attributes)->assertRedirect(route('books.index'));
        $book = Book::where('title', 'The Example Book')->firstOrFail();
        $this->assertSame($user->id, $book->user_id);
        $this->assertDatabaseHas('books', ['title' => 'The Example Book', 'unit_price' => '125.50']);
        $this->get(route('books.show', $book))->assertOk()->assertSee('A short description.');

        $this->put(route('books.update', $book), array_merge($attributes, [
            'title' => 'Updated Title',
            'unit_price' => '0',
        ]))->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title', 'unit_price' => '0.00']);

        $this->delete(route('books.destroy', $book))->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_book_fields_reject_negative_prices_and_invalid_choices(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => 'Invalid Book',
                'author' => 'A. Writer',
                'category' => 'Comics',
                'unit_price' => '-1.00',
                'description' => 'Description.',
                'availability_status' => 'Maybe',
            ])
            ->assertRedirect(route('books.create'))
            ->assertSessionHasErrors(['category', 'unit_price', 'availability_status'])
            ->assertSessionHas('_old_input.title', 'Invalid Book');

        $this->assertDatabaseCount('books', 0);

        $this->post(route('books.store'), [
            'title' => 'Invalid Price',
            'author' => 'A. Writer',
            'category' => 'Academic',
            'unit_price' => 'not-a-number',
            'description' => 'Description.',
            'availability_status' => 'Available',
        ])->assertSessionHasErrors('unit_price');
    }

    public function test_administrator_cannot_view_another_users_book(): void
    {
        $book = Book::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('books.show', $book))->assertNotFound();
    }
}
