<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'brand', 'color', 'state', 'description', 'file', 'price', 'user_id'];

    // RELATION AVEC LA TABLE CATEGORIES
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'article_category');
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
