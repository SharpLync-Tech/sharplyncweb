<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class AboutTimelineItem extends Model
{
    protected $table = 'about_timeline_items';

    protected $fillable = [
        'year',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Only active timeline items
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Sort timeline items
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
