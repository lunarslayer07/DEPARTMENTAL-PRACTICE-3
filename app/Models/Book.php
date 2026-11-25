<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    public const CATEGORIES = ['Fiction', 'Nonfiction', 'Academic', "Children's Books"];

    public const AVAILABILITY_STATUSES = ['Available', 'Unavailable'];

    protected $fillable = [
        'title',
        'author',
        'category',
        'unit_price',
        'description',
        'availability_status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
