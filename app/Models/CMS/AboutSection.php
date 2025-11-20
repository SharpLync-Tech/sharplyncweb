<?php

namespace App\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    protected $table = 'about_sections';

    protected $fillable = [
        'title',
        'content',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Only active blocks
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Default ordering
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
