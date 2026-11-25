<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlatBill extends Model
{
    protected $fillable = [
        'flat_id',
        'type',
        'title',
        'amount',
        'billing_month',
        'due_date',
        'is_paid',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FlatBillPayment::class);
    }
}
