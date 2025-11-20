<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $table = 'post_categories';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    // A category has many posts
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
