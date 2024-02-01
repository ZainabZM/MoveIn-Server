<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'file', 'brand', 'color', 'state', 'description', 'price', 'user_id'];

    // RELATION AVEC LA TABLE CATEGORIES
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    // RELATION AVEC LA TABLE FAVOURITES
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    // RELATION AVEC LA TABLE FILES
    //  public function pictures(): HasMany
    //  {
    //      return $this->hasMany(File::class);
    //  }
}
