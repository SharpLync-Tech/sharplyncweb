<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Quick lookup by slug: Page::slug('about')->first();
    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    // Only active pages
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
