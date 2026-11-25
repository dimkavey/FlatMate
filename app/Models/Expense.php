<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    protected $fillable = [
        'flat_id',
        'user_id',
        'title',
        'amount',
        'category',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ExpensePayment::class);
    }
}
