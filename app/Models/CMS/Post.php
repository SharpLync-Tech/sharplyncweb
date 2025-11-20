<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // A post belongs to a category
    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    // Only published posts
    public function scopePublished($query)
    {
        return $query->where('is_published', 1)->whereNotNull('published_at');
    }
}
