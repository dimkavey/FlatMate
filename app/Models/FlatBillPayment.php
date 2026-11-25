<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlatBillPayment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'flat_bill_id',
        'user_id',
        'status',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(FlatBill::class, 'flat_bill_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
