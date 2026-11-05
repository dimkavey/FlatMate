<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flat extends Model
{
    protected $fillable = ['name', 'invite_code'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
