<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['comment', 'rating', 'buyer_id', 'seller_id'];

    // RELATION AVEC LA TABLE USERS
    public function users(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
