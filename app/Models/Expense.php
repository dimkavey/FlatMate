<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_id',
        'title',
        'amount',
        'category',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
